<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cert;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CertVerification;
use App\Models\CertComment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Template;

class CertController extends Controller
{
    public function index(Request $request)
    {
        $query = Cert::with('creator');

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Apply certificate type filter
        if ($request->filled('cert_type') && $request->cert_type !== 'all') {
            $query->where('cert_type', $request->cert_type);
        }

        // Apply ISO number filter
        if ($request->filled('iso_num') && $request->iso_num !== 'all') {
            $query->where('iso_num', $request->iso_num);
        }

        // Apply name filter
        if ($request->filled('comp_name') && $request->comp_name !== 'all') {
            $query->where('comp_name', $request->comp_name);
        }

        // Apply date filter
        if ($request->filled('date_type') && $request->filled('date_value') && $request->date_value !== 'all') {
            $dateField = $request->date_type;
            $dateValue = $request->date_value;
            $query->whereDate($dateField, $dateValue);
        }

        // Apply search if provided (quick search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cert_type', 'like', "%{$search}%")
                    ->orWhere('iso_num', 'like', "%{$search}%")
                    ->orWhere('comp_name', 'like', "%{$search}%")
                    ->orWhere('comp_address1', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortField = $request->get('sort_field', 'created_at'); // Default sort field
        $sortDirection = $request->get('sort_direction', 'desc'); // Default sort direction

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['cert_type', 'comp_name', 'created_at', 'last_edited_at', 'status'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        // Get all certificates matching the query
        $cert = $query->get();

        // Get unique values for dropdowns
        $certTypes = Cert::select('cert_type')->distinct()->pluck('cert_type');
        $isoNumbers = Cert::select('iso_num')->distinct()->pluck('iso_num');
        $companyNames = Cert::select('comp_name')->distinct()->pluck('comp_name');

        // Get unique dates
        $regDates = Cert::select('reg_date')->distinct()->pluck('reg_date');
        $issueDates = Cert::select('issue_date')->distinct()->pluck('issue_date');
        $expDates = Cert::select('exp_date')->distinct()->pluck('exp_date');

        // Get status count for tabs
        $statusCounts = [
            'all' => Cert::count(),
            'pending_review' => Cert::where('status', 'pending_review')->count(),
            'pending_client_verification' => Cert::where('status', 'pending_client_verification')->count(),
            'client_verified' => Cert::where('status', 'client_verified')->count(),
            'need_revision' => Cert::where('status', 'need_revision')->count(),
            'pending_hod_approval' => Cert::where('status', 'pending_hod_approval')->count(),
            'certificate_issued' => Cert::where('status', 'certificate_issued')->count()
        ];

        return view('certificates.index', [
            'certs' => $cert,
            'certTypes' => $certTypes,
            'isoNumbers' => $isoNumbers,
            'companyNames' => $companyNames,
            'regDates' => $regDates,
            'issueDates' => $issueDates,
            'expDates' => $expDates,
            'statusCounts' => $statusCounts,
            'currentSortField' => $sortField,
            'currentSortDirection' => $sortDirection
        ]);
    }

    public function create()
    {
        $clients = Client::all();
        return view('certificates.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cert_type' => 'required|string|max:255',
            'iso_num' => 'required|string|max:255',
            'comp_name' => 'required|string|max:255',
            'comp_address1' => 'required|string|max:255',
            'comp_address2' => 'nullable|string|max:255',
            'comp_address3' => 'nullable|string|max:255',
            'comp_phone1' => 'nullable|string|max:15',
            'phone1_name' => 'nullable|string|max:255',
            'comp_phone2' => 'nullable|string|max:15',
            'phone2_name' => 'nullable|string|max:255',
            'reg_date' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $data['status'] = 'pending_review'; // Default status
        $data['created_by'] = Auth::id(); // Set the creator of the certificate

        // Don't set issue_date and exp_date during creation - they will be set when certificate is issued
        $data['issue_date'] = null;
        $data['exp_date'] = null;

        $newCert = Cert::create($data);

        // Create or update client information if client_id is empty or not selected
        if (empty($request->client_id)) {
            // If no client was selected, create a new client
            $clientData = [
                'comp_name' => $request->comp_name,
                'comp_address1' => $request->comp_address1,
                'comp_address2' => $request->comp_address2,
                'comp_address3' => $request->comp_address3,
                'comp_phone1' => $request->comp_phone1,
                'comp_phone2' => $request->comp_phone2,
                'phone1_name' => $request->phone1_name ?? '',
                'phone2_name' => $request->phone2_name ?? '',
            ];

            Client::create($clientData);
        }

        return redirect()->route('certificates.index')->with('success', 'Certificate created successfully.');
    }

    public function edit(Cert $cert)
    {
        return view('certificates.edit', ['cert' => $cert]);
    }

    public function update(Request $request, Cert $cert)
    {
        // Base validation rules
        $validationRules = [
            'cert_type' => 'required|string|max:255',
            'iso_num' => 'required|string|max:255',
            'comp_name' => 'required|string|max:255',
            'comp_address1' => 'required|string|max:255',
            'comp_address2' => 'nullable|string|max:255',
            'comp_address3' => 'nullable|string|max:255',
            'comp_phone1' => 'nullable|string|max:15',
            'comp_phone2' => 'nullable|string|max:15',
            'phone1_name' => 'required|string|max:255',
            'phone2_name' => 'nullable|string|max:255',
            'reg_date' => 'required|date',
        ];

        // Only validate issue_date and exp_date if certificate is already issued
        if ($cert->status === 'certificate_issued' && $cert->issue_date) {
            $validationRules['issue_date'] = 'required|date';
            $validationRules['exp_date'] = 'required|date|after:issue_date';
        }

        $data = $request->validate($validationRules);

        $data['last_edited_at'] = now();
        $data['last_edited_by'] = Auth::id();

        if ($cert->status === 'need_revision') {
            if ($cert->revision_source === 'hod') {
                $data['status'] = 'pending_hod_approval';
            } else {
                $data['status'] = 'pending_client_verification';
            }
            $data['revision_source'] = null;
        }

        $cert->update($data);

        return redirect()->route('certificates.index')->with('success', 'Certificate updated successfully.');
    }

    public function destroy(Cert $cert)
    {
        $cert->delete();
        return redirect()->route('certificates.index')->with('success', 'Certificate deleted successfully.');
    }

    public function preview(Cert $cert)
    {
        // Get verification details if they exist
        $verification = CertVerification::where('cert_id', $cert->id)->latest()->first();
        $comments = CertComment::where('cert_id', $cert->id)->orderBy('created_at', 'desc')->get();

        return view('certificates.preview', [
            'cert' => $cert,
            'verification' => $verification,
            'comments' => $comments
        ]);
    }

    public function view(Request $request)
    {
        $query = Cert::query();

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Apply certificate type filter
        if ($request->filled('cert_type') && $request->cert_type !== 'all') {
            $query->where('cert_type', $request->cert_type);
        }

        // Apply ISO number filter
        if ($request->filled('iso_num') && $request->iso_num !== 'all') {
            $query->where('iso_num', $request->iso_num);
        }

        // Apply name filter
        if ($request->filled('comp_name') && $request->comp_name !== 'all') {
            $query->where('comp_name', $request->comp_name);
        }

        // Apply date filter
        if ($request->filled('date_type') && $request->filled('date_value') && $request->date_value !== 'all') {
            $dateField = $request->date_type;
            $dateValue = $request->date_value;
            $query->whereDate($dateField, $dateValue);
        }

        // Apply search if provided (quick search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cert_type', 'like', "%{$search}%")
                    ->orWhere('iso_num', 'like', "%{$search}%")
                    ->orWhere('comp_name', 'like', "%{$search}%")
                    ->orWhere('comp_address1', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortField = $request->get('sort_field', 'issue_date'); // Default sort field
        $sortDirection = $request->get('sort_direction', 'desc'); // Default sort direction

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['cert_type', 'comp_name', 'issue_date', 'exp_date'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'issue_date';
        }

        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        // Get all certificates matching the query
        $cert = $query->get();

        // Get unique values for dropdowns
        $certTypes = Cert::select('cert_type')->distinct()->pluck('cert_type');
        $isoNumbers = Cert::select('iso_num')->distinct()->pluck('iso_num');
        $companyNames = Cert::select('comp_name')->distinct()->pluck('comp_name');

        // Get unique dates
        $regDates = Cert::select('reg_date')->distinct()->pluck('reg_date');
        $issueDates = Cert::select('issue_date')->distinct()->pluck('issue_date');
        $expDates = Cert::select('exp_date')->distinct()->pluck('exp_date');

        // Get status count for tabs
        $statusCounts = [
            'all' => Cert::count(),
            'pending_review' => Cert::where('status', 'pending_review')->count(),
            'pending_client_verification' => Cert::where('status', 'pending_client_verification')->count(),
            'client_verified' => Cert::where('status', 'client_verified')->count(),
            'need_revision' => Cert::where('status', 'need_revision')->count(),
            'pending_hod_approval' => Cert::where('status', 'pending_hod_approval')->count(),
            'certificate_issued' => Cert::where('status', 'certificate_issued')->count()
        ];

        return view('certificates.view', [
            'certs' => $cert,
            'certTypes' => $certTypes,
            'isoNumbers' => $isoNumbers,
            'companyNames' => $companyNames,
            'regDates' => $regDates,
            'issueDates' => $issueDates,
            'expDates' => $expDates,
            'statusCounts' => $statusCounts,
            'currentSortField' => $sortField,
            'currentSortDirection' => $sortDirection
        ]);
    }

    public function hodApproval(Request $request, Cert $cert)
    {
        $action = $request->input('action');

        if ($action === 'approve') {
            // Set issue date to today when certificate is approved
            $issueDate = now()->toDateString();
            $expDate = now()->addYears(3)->toDateString(); // 3 years from issue date

            $cert->status = 'certificate_issued';
            $cert->hod_approved = true;
            $cert->issue_date = $issueDate;
            $cert->exp_date = $expDate;
            $cert->save();

            // Generate the final certificate PDF
            $this->generateCertificatePDF($cert);

            return redirect()->route('certificates.preview', $cert->id)
                ->with('success', 'Certificate approved and final PDF generated. Issue date set to today, expiry date set to 3 years from now.');
        } elseif ($action === 'reject') {
            // Validate the comment
            $request->validate([
                'comment' => 'required|string|max:1000',
            ]);

            $cert->status = 'need_revision';
            $cert->revision_source = 'hod';
            $cert->hod_approved = false;
            $cert->save();

            // Save the rejection comment
            CertComment::create([
                'cert_id' => $cert->id,
                'comment' => $request->input('comment'),
                'commented_by' => 'HoD',
                'comment_type' => 'revision_request'
            ]);

            return redirect()->route('certificates.preview', $cert->id)
                ->with('error', 'Certificate sent back for revision.');
        }

        return redirect()->back()->with('error', 'Invalid action.');
    }

    /**
     * Generate a verification link for a certificate
     *
     * @param Cert $cert
     * @return \Illuminate\View\View
     */
    public function getVerificationLink(Cert $cert)
    {
        $verification = CertVerification::where('cert_id', $cert->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$verification) {
            $verification = CertVerification::create([
                'cert_id' => $cert->id,
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
                'is_verified' => false
            ]);
        }

        $verificationUrl = route('certificates.verify', ['token' => $verification->token]);

        return view('certificates.verification-link', [
            'verificationUrl' => $verificationUrl,
            'cert' => $cert,
            'verification' => $verification
        ]);
    }

    /**
     * Renew the verification link for a certificate
     *
     * @param Cert $cert
     * @return \Illuminate\Http\RedirectResponse
     */
    public function renewVerificationLink(Cert $cert)
    {
        $verification = CertVerification::where('cert_id', $cert->id)->latest()->first();

        if ($verification) {
            $verification->token = Str::random(64);
            $verification->expires_at = now()->addDays(7);
            $verification->is_verified = false;
            $verification->verified_at = null;
            $verification->save();
        } else {
            $verification = CertVerification::create([
                'cert_id' => $cert->id,
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
                'is_verified' => false
            ]);
        }

        return redirect()->route('certificates.preview', $cert->id)->with('success', 'Verification link renewed successfully.');
    }

    /**
     * Show verification page for a certificate
     *
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function verify($token)
    {
        $verification = CertVerification::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $cert = Cert::findOrFail($verification->cert_id);

        $comments = CertComment::where('cert_id', $cert->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('certificates.verify', [
            'cert' => $cert,
            'verification' => $verification,
            'comments' => $comments
        ]);
    }

    /**
     * Process the verification response
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processVerification(Request $request, $token)
    {
        $verification = CertVerification::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $cert = Cert::findOrFail($verification->cert_id);
        $action = $request->input('action');

        if ($action == 'verify') {
            $verification->is_verified = true;
            $verification->verified_at = now();
            $verification->save();

            $cert->status = 'client_verified';
            $cert->save();

            if ($request->filled('comment')) {
                CertComment::create([
                    'cert_id' => $cert->id,
                    'comment' => 'Client has verified the certificate',
                    'commented_by' => $request->input('name', 'Client'),
                    'comment_type' => 'verification'
                ]);
            }

            return redirect()->route('certificates.verify', ['token' => $token])
                ->with('success', 'Certificate verified successfully.');
        } elseif ($action == 'reject') {

            $request->validate([
                'comment' => 'required|string|max:255',
                'name' => 'required|string|max:255'
            ], [
                'comment.required' => 'Please provide a comment for rejection.',
                'name.required' => 'Please provide your name.'
            ]);

            $verification->is_verified = false;
            $verification->save();

            $cert->status = 'need_revision';
            $cert->revision_source = 'client';
            $cert->save();

            CertComment::create([
                'cert_id' => $cert->id,
                'comment' => $request->input('comment'),
                'commented_by' => $request->input('name'),
                'comment_type' => 'revision_request'
            ]);
        }

        return redirect()->route('certificates.verify', ['token' => $token])
            ->with('success', 'Certificate verification status updated successfully.');
    }

    // New method to fetch client data for Ajax requests
    public function getClientData($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client);
    }

    public function confirm(Request $request, $id)
    {
        $cert = Cert::findOrFail($id);

        // Example: set status to pending_review or any logic you want
        $cert->status = 'pending_client_verification';
        $cert->save();

        $verification = CertVerification::where('cert_id', $cert->id)->latest()->first();
        if (!$verification) {
            $verification = CertVerification::create([
                'cert_id' => $cert->id,
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
                'is_verified' => false
            ]);
        }

        return redirect()->route('certificates.index', $cert->id)
            ->with('success', 'Certificate draft confirmed successfully.');
    }

    protected function generatePDFWithData(Cert $cert, $isDraft = false)
    {
        // 1. Get the active template for this cert type
        $template = Template::where('cert_type', $cert->cert_type)
            ->where('is_active', true)
            ->first();

        if (!$template || !Storage::disk('public')->exists($template->file_path)) {
            throw new \Exception('No active template found for this certificate type.');
        }

        // 2. Get full path of template
        $templatePath = Storage::disk('public')->path($template->file_path);

        // 3. Create PDF using FPDI
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pageCount = $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx);

        // 4. Add cert data overlay with consistent positioning
        $pdf->SetTextColor(0, 0, 0);
        $pageWidth = $pdf->GetPageWidth();

        // Company Name - consistent positioning
        $pdf->SetFont('Helvetica', 'B', 26);
        $textWidth = $pdf->GetStringWidth($cert->comp_name);
        $pdf->SetXY(($pageWidth - $textWidth) / 2, 90);
        $pdf->Write(8, $cert->comp_name);

        // Address - consistent positioning
        $pdf->SetFont('Helvetica', '', 12);

        $address = $cert->comp_address1;
        if ($cert->comp_address2) {
            $address .= "\n" . $cert->comp_address2; // Use \n for line break
        }
        if ($cert->comp_address3) {
            $address .= "\n" . $cert->comp_address3;
        }

        // Center-align using MultiCell
        $pdf->SetXY(20, 102); // X = margin, Y = vertical position
        $pdf->MultiCell(
            $pageWidth - 40, // width with 20mm horizontal margin (10mm on each side)
            6,               // height per line (adjust as needed)
            $address,
            0,               // no border
            'C'              // center align
        );

        // ISO Number - consistent positioning
        $pdf->SetFont('Helvetica', 'B', 28);
        $textWidth = $pdf->GetStringWidth($cert->iso_num);
        $pdf->SetXY(($pageWidth - $textWidth) / 2, 145);
        $pdf->Write(8, $cert->iso_num);

        // Certificate Number
        $pdf->SetFont('Helvetica', '', 14);
        $pdf->SetXY(87, 160.5);
        if ($isDraft) {
            $pdf->Write(8, $cert->cert_number ?? '[Certificate Number]');
        } else {
            $pdf->Write(8, $cert->cert_number);
        }

        // Registration Date
        $pdf->SetXY(88, 167.5);
        $pdf->Write(8, $cert->reg_date->format('d M Y'));

        // Issue Date
        $pdf->SetXY(74, 174.5);
        if ($isDraft) {
            $pdf->Write(8, '[Issue Date]');
        } else {
            $pdf->Write(8, $cert->issue_date->format('d M Y'));
        }

        // Expiry Date
        $pdf->SetXY(76, 181.5);
        if ($isDraft) {
            $pdf->Write(8, '[Expiry Date]');
        } else {
            $pdf->Write(8, $cert->exp_date->format('d M Y'));
        }

        return $pdf;
    }

    protected function generateCertificatePDF(Cert $cert)
    {
        $pdf = $this->generatePDFWithData($cert, false);

        // Prepare output file path
        $fileName = 'certificates/' . $cert->id . '_' . now()->format('YmdHis') . '.pdf';
        $outputPath = Storage::disk('public')->path($fileName);

        // Save the PDF to storage
        $pdf->Output($outputPath, 'F');

        // Save the path to the cert for preview/download
        $cert->pdf_path = $fileName;
        $cert->save();

        return $fileName;
    }

    public function previewDraft(Cert $cert)
    {
        try {
            $pdf = $this->generatePDFWithData($cert, true);

            // Output inline (no download)
            return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="certificate_draft.pdf"');
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    public function assignNumber(Request $request, Cert $cert)
    {
        $request->validate([
            'cert_number' => 'required|string|max:255',
        ]);

        $cert->cert_number = $request->input('cert_number');

        if ($cert->status === 'client_verified') {
            $cert->status = 'pending_hod_approval';
        }

        $cert->save();

        return redirect()->route('certificates.preview', $cert->id)
            ->with('success', 'Certificate number assigned successfully.');
    }

    public function showAssignNumberForm(Cert $cert)
    {
        return view('certificates.assign-number', compact('cert'));
    }

    public function previewFinal(Cert $cert)
    {
        $path = storage_path('app/public/' . $cert->pdf_path);

        if (!file_exists($path)) {
            abort(404, 'Certificate file not found.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="final_certificate.pdf"',
        ]);
    }
}
