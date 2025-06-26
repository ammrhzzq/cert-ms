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
use Illuminate\Support\Str;
use App\Models\Template;
use setasign\Fpdi\PdfReader\PdfReaderException;
use setasign\Fpdi\PdfParser\PdfParserException;

class CertController extends Controller
{
    public function index(Request $request) {
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
            $query->where(function($q) use ($search) {
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
    
    public function create() {
        $clients = Client::all(); // Adjust the model namespace if needed
        return view('certificates.create', compact('clients'));
    }

    public function store(Request $request){
        $data = $request->validate([
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
            'issue_date' => 'required|date',
            'exp_date' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $data['status'] = 'pending_review'; // Default status
        $data['created_by'] = Auth::id(); // Set the creator of the certificate

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

    public function edit(Cert $cert) {
        return view('certificates.edit', ['cert' => $cert]);
    }

    public function update(Request $request, Cert $cert) {
        $data = $request->validate([
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
            'issue_date' => 'required|date',
            'exp_date' => 'required|date'
        ]);

        $fieldsToCheck = [
            'cert_type', 'iso_num', 'comp_name', 'comp_address1', 'comp_address2', 'comp_address3',
            'comp_phone1', 'phone1_name', 'comp_phone2', 'phone2_name',
            'reg_date', 'issue_date', 'exp_date'
        ];

        $hasChanges = false;
        foreach ($fieldsToCheck as $field) {
            // Use loose comparison to handle null vs empty string
            if (($cert->$field ?? null) != ($data[$field] ?? null)) {
                $hasChanges = true;
                break;
            }
        }

        if ($hasChanges) {
            $data['last_edited_at'] = now();
            
            if ($cert->status === 'need_revision') {
                if ($cert->revision_source === 'hod') {
                    $data['status'] = 'pending_hod_approval';
                }  else {
                $data['status'] = 'pending_client_verification';
            }
                $data['revision_source'] = null;
            }

            // $data['last_edited_by'] = auth()->id(); // if you track editor
        $cert->update($data);

        // Automatically generate the certificate PDF if status is final approved
        if ($cert->status === 'certificate_issued') {
            // Generate the PDF
            $this->generateCertificatePDF($cert); // Directly pass the Cert instance
        }

        if ($cert->status === 'pending_client_verification') {
            CertComment::create([
                'cert_id' => $cert->id,
                'comment' => 'The certificate has been updated. Please review the changes.',
                'commented_by' => auth()->user()->name,
                'comment_type' => 'internal',
                'revision_source' => 'system'
            ]);
        }

        return redirect()->route('certificates.index')->with('success', 'Certificate updated successfully.');
        } else {
            // No changes, do not update last_edited_at
            return redirect()->route('certificates.index')->with('info', 'No changes detected.');
        }
    }

    public function destroy(Cert $cert) {
        $cert->delete();
        return redirect()->route('certificates.index')->with('success', 'Certificate deleted successfully.');
    }

    public function preview(Cert $cert) {
        // Get verification details if they exist
        $verification = CertVerification::where('cert_id', $cert->id)->latest()->first();
        $comments = CertComment::where('cert_id', $cert->id)->orderBy('created_at', 'desc')->get();

        $verificationUrl = $verification
        ? route('certificates.verify', ['token' => $verification->token])
        : null;

        return view('certificates.preview', [
            'cert' => $cert,
            'verification' => $verification,
            'comments' => $comments,
            'verificationUrl' => $verificationUrl
        ]);
    }

    public function view(Request $request) {
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
            $query->where(function($q) use ($search) {
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
            $cert->status = 'certificate_issued';
            $cert->hod_approved = true;
            $cert->save();

            // Generate the final certificate PDF
            $this->generateCertificatePDF($cert);

            return redirect()->route('certificates.preview', $cert->id)
                ->with('success', 'Certificate approved and final PDF generated.');

        } elseif ($action === 'reject') {
            $request->validate([
                'hod_reject_comment' => 'required|string|max:255',
            ]);

            $cert->status = 'need_revision';
            $cert->revision_source = 'hod';
            $cert->hod_approved = false;
            $cert->save();

            CertComment::create([
                'cert_id' => $cert->id,
                'comment' => $request->input('hod_reject_comment'),
                'commented_by' => auth()->user()->name ?? 'HOD',
                'comment_type' => 'revision_request',
                'revision_source' => 'hod'
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
            'comments' => $comments,
            'revision_source' => 'client'
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

            CertComment::create([
                'cert_id' => $cert->id,
                'comment' => 'Verified the certificate',
                'commented_by' => $cert->comp_name,
                'comment_type' => 'verification',
                'revision_source' => 'client'
            ]);
            
            return redirect()->route('certificates.verify', ['token' => $token])
                ->with('success', 'Certificate verified successfully.');
        }
        elseif ($action == 'reject') {

            $request->validate([
                'comment' => 'required|string|max:2000'
            ], [
                'comment.required' => 'Please provide a comment for rejection.'
            ]);

            $verification->is_verified = false;
            $verification->save();

            $cert->status = 'need_revision';
            $cert->revision_source = 'client';
            $cert->save();

            CertComment::create([
                'cert_id' => $cert->id,
                'comment' => $request->input('comment'),
                'commented_by' => $cert->comp_name,
                'comment_type' => 'revision_request',
                'revision_source' => 'client'
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

    protected function generateCertificatePDF(Cert $cert)
    {
        // 1. Get the active template for this cert type
        $template = Template::where('cert_type', $cert->cert_type)
            ->where('is_active', true)
            ->first();

        if (!$template || !Storage::disk('public')->exists($template->file_path)) {
            throw new \Exception('No active template file found for this certificate type.');
        }

        // 2. Get the full path to the template PDF
        $templatePath = Storage::disk('public')->path($template->file_path);

        // 3. Prepare output file path
        $fileName = 'certificates/' . $cert->id . '_' . now()->format('YmdHis') . '.pdf';
        $outputPath = Storage::disk('public')->path($fileName);

        // 4. Use FPDI to import the template and overlay data
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pageCount = $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx);

        // 4. Add cert data overlay
        $pdf->SetTextColor(0, 0, 0);
        $pageWidth = $pdf->GetPageWidth();

        $pdf->SetFont('Helvetica', 'B', 32);
        $textWidth = $pdf->GetStringWidth($cert->comp_name);
        $pdf->SetXY(($pageWidth - $textWidth) / 2, 90);
        $pdf->Write(8, $cert->comp_name);

        $pdf->SetFont('Helvetica', '', 20);
        $address = $cert->comp_address1;
        if ($cert->comp_address2) {
            $address .= ', ' . $cert->comp_address2;
        }
        if ($cert->comp_address3) {
            $address .= ', ' . $cert->comp_address3;
        }

        // Center-align using MultiCell
        $pdf->SetXY(10, 105); // X = margin, Y = vertical position
        $pdf->MultiCell(
            $pageWidth - 20, // width with 10mm left/right margins
            10,              // height per line
            $address,
            0,               // no border
            'C'              // center align
        );

        $pdf->SetFont('Helvetica', 'B', 24);
        $textWidth = $pdf->GetStringWidth($cert->iso_num);
        $pdf->SetXY(($pageWidth - $textWidth) / 2, 145);
        $pdf->Write(8, $cert->iso_num);

        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetXY(78, 160.5);
        $pdf->Write(8, $cert->cert_number);

        $pdf->SetXY(81, 167.5);
        $pdf->Write(8, $cert->reg_date->format('d M Y'));

        $pdf->SetXY(66, 174.5);
        $pdf->Write(8, $cert->issue_date->format('d M Y'));

        $pdf->SetXY(68, 181.5);
        $pdf->Write(8, $cert->exp_date->format('d M Y'));

        // Save the PDF to storage
        $pdf->Output($outputPath, 'F');

        // Save the path to the cert for preview/download
        $cert->pdf_path = $fileName;
        $cert->save();

        return $fileName;
    }

    public function previewDraft(Cert $cert)
    {
        // 1. Get the active template for this cert type
        $template = Template::where('cert_type', $cert->cert_type)
            ->where('is_active', true)
            ->first();

        if (!$template || !Storage::disk('public')->exists($template->file_path)) {
            abort(404, 'No active template found for this certificate type.');
        }

        // 2. Get full path of template
        $templatePath = Storage::disk('public')->path($template->file_path);

        // 3. Create PDF using FPDI
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pageCount = $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx);

        $pdf->Image(public_path('images/draft_watermark.png'), 0, 10, 200, 0, 'PNG');

        // 4. Add cert data overlay
        $pdf->SetTextColor(0, 0, 0);
        $pageWidth = $pdf->GetPageWidth();

        $pdf->SetFont('Helvetica', 'B', 32);
        $textWidth = $pdf->GetStringWidth($cert->comp_name);
        $pdf->SetXY(($pageWidth - $textWidth) / 2, 90);
        $pdf->Write(8, $cert->comp_name);

        $pdf->SetFont('Helvetica', '', 20);
        $address = $cert->comp_address1;
        if ($cert->comp_address2) {
            $address .= ', ' . $cert->comp_address2;
        }
        if ($cert->comp_address3) {
            $address .= ', ' . $cert->comp_address3;
        }

        // Center-align using MultiCell
        $pdf->SetXY(10, 105); // X = margin, Y = vertical position
        $pdf->MultiCell(
            $pageWidth - 20, // width with 10mm left/right margins
            10,              // height per line
            $address,
            0,               // no border
            'C'              // center align
        );

        $pdf->SetFont('Helvetica', 'B', 24);
        $textWidth = $pdf->GetStringWidth($cert->iso_num);
        $pdf->SetXY(($pageWidth - $textWidth) / 2, 145);
        $pdf->Write(8, $cert->iso_num);

        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetXY(78, 160.5);
        if ($cert->status === 'pending_hod_approval' && $cert->cert_number) {
            $pdf->Write(8, $cert->cert_number);
        } else {
            $pdf->Write(8, 'Available for Final Approval');
        }

        $pdf->SetXY(81, 167.5);
        $pdf->Write(8, $cert->reg_date->format('d M Y'));

        $pdf->SetXY(66, 174.5);
        $pdf->Write(8, $cert->issue_date->format('d M Y'));

        $pdf->SetXY(68, 181.5);
        $pdf->Write(8, $cert->exp_date->format('d M Y'));

        // 5. Output inline (no download)
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="certificate_draft.pdf"');
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
        // Generate a safe file name
        $filename =  $cert->cert_number . '_' . 'certificate_' . Str::slug($cert->comp_name) . '.pdf';

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}