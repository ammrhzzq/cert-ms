<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cert;  
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CertVerification;
use App\Models\CertComment;

class CertController extends Controller
{
    public function index(Request $request)
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
            'client_verified' => Cert::where('status', 'client_verified')->count(),
            'need_revision' => Cert::where('status', 'need_revision')->count(),
            'pending_hod_approval' => Cert::where('status', 'pending_hod_approval')->count()
        ];

        return view('certificates.index', [
            'certs' => $cert,
            'certTypes' => $certTypes,
            'isoNumbers' => $isoNumbers,
            'companyNames' => $companyNames,
            'regDates' => $regDates,
            'issueDates' => $issueDates,
            'expDates' => $expDates,
            'statusCounts' => $statusCounts
        ]);
    }

    public function create()
    {
        // Get all clients for the dropdown
        $clients = Client::orderBy('comp_name')->get();
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
            'issue_date' => 'required|date',
            'exp_date' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        // Set default empty values for phone name fields if not provided
        if (!isset($data['phone1_name'])) {
            $data['phone1_name'] = '';
        }
        
        if (!isset($data['phone2_name'])) {
            $data['phone2_name'] = '';
        }

        $data['status'] = 'pending_review'; // Default status

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
            'issue_date' => 'required|date',
            'exp_date' => 'required|date',
            'status' => 'required|string',
        ]);

        // Set default empty values for phone name fields if not provided
        if (!isset($data['phone1_name'])) {
            $data['phone1_name'] = '';
        }
        
        if (!isset($data['phone2_name'])) {
            $data['phone2_name'] = '';
        }

        $data['last_edited_at'] = now();
        //$data['last_edited_by'] = auth()->id();

        $cert->update($data);

        // Automatically generate the certificate PDF if status is final approved
        if ($cert->status === 'client_verified') {
            // Generate the PDF
            $this->generateCertificatePDF($cert); // Directly pass the Cert instance
        }
        return redirect()->route('certificates.index')->with('success', 'Certificate updated successfully.');
    }

    public function destroy(Cert $cert)
    {
        $cert->delete();
        return redirect()->route('certificates.index')->with('success', 'Certificate deleted successfully.');
    }

    public function show(Cert $cert) {
        // Get verification details if they exist
        $verification = CertVerfication::where('cert_id', $cert->id)->latest()->first();
        $comments = CertComment::where('cert_id', $cert->id)->orderBy('created_at', 'desc')->get();

        return view('certificates.show', [
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
            'client_verified' => Cert::where('status', 'client_verified')->count(),
            'need_revision' => Cert::where('status', 'need_revision')->count(),
            'pending_hod_approval' => Cert::where('status', 'pending_hod_approval')->count()
        ];

        return view('certificates.view', [
            'certs' => $cert,
            'certTypes' => $certTypes,
            'isoNumbers' => $isoNumbers,
            'companyNames' => $companyNames,
            'regDates' => $regDates,
            'issueDates' => $issueDates,
            'expDates' => $expDates,
            'statusCounts' => $statusCounts
        ]);
    }

    public function approveByHod($id) {
        $cert = Cert::findOrFail($id);

        // Step 1: Approve the cert
        $cert->hod_approved = true;
        $cert->save();

        // Step 2: Auto-generated the certificate PDF
        $this->generateCertificatePDF($cert);

        // Optional: return success message or redirect
        return redirect()->back()->with('success', 'Certificate approved and generated successfully.');
    }

    private function generateCertificatePDF(Cert $cert){
        $template = \App\Models\Template::where('cert_type', $cert->cert_type)
            ->where('is_active', true)
            ->latest('version')
            ->first();

        if (!$template || !Storage::exists($template->file_path)) {
            throw new \Exception('No active template found for {$cert->cert_type}');
        }

        $templatePath = storage_path('app/' . $template->file_path);
        $savePath = 'public/generated_certificates/' . $cert->id . '_certificate.pdf';

        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx);

        // Set font and size
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(0, 0, 0);

        $coords = [
            'cert_type' => [50, 20],
            'iso_num' => [50, 30],
            'comp_name' => [50, 40],
            'comp_address1' => [50, 50],
            'comp_address2' => [50, 60],
            'comp_address3' => [50, 70],
            'reg_date' => [50, 80],
            'issue_date' => [50, 90],
            'exp_date' => [50, 100]
        ];
        $pdf->SetXY($coords['cert_type'][0], $coords['cert_type'][1]);
        $pdf->Write(0, 'Certificate Type: ' . $cert->cert_type);
        $pdf->SetXY($coords['iso_num'][0], $coords['iso_num'][1]);
        $pdf->Write(0, 'ISO Number: ' . $cert->iso_num);
        $pdf->SetXY($coords['comp_name'][0], $coords['comp_name'][1]);  
        $pdf->Write(0, 'Company Name: ' . $cert->comp_name);
        $pdf->SetXY($coords['comp_address1'][0], $coords['comp_address1'][1]);
        $pdf->Write(0, 'Company Address: ' . $cert->comp_address1);
        if ($cert->comp_address2) {
            $pdf->SetXY($coords['comp_address2'][0], $coords['comp_address2'][1]);
            $pdf->Write(0, 'Company Address 2: ' . $cert->comp_address2);
        }
        if ($cert->comp_address3) {
            $pdf->SetXY($coords['comp_address3'][0], $coords['comp_address3'][1]);
            $pdf->Write(0, 'Company Address 3: ' . $cert->comp_address3);
        }
        $pdf->SetXY($coords['reg_date'][0], $coords['reg_date'][1]);
        $pdf->Write(0, 'Registration Date: ' . $cert->reg_date->format('d-m-Y'));
        $pdf->SetXY($coords['issue_date'][0], $coords['issue_date'][1]);
        $pdf->Write(0, 'Issue Date: ' . $cert->issue_date->format('d-m-Y'));
        $pdf->SetXY($coords['exp_date'][0], $coords['exp_date'][1]);
        $pdf->Write(0, 'Expiry Date: ' . $cert->exp_date->format('d-m-Y'));

        $pdfContent = $pdf->Output('S');
        Storage::put($savePath, $pdfContent); // Ensure the directory exists


        try {
            $cert->update([
                'generate_pdf_path' => $savePath,
            ]);
            $cert->update(['status' => 'client_verified']);
        } catch (\Exception $e) {
            \Log::error("PDF generation failed for Cert ID {$cert->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate certificate.');
        }
    }

    public function confirm(Request $request, Cert $cert) {

        // Authorization
        $userRole = auth()->user()->role;
        if (!in_array($userRole, ['hod', 'manager'])) {
            abort(403, 'Unauthorized action.');
        }

        // Confirmation text check
        $request->validate([
            'confirmation_text' => ['required', function ($attribute, $value, $fail) {
                if (strtoupper($value) !== 'CONFIRM') {
                    $fail('You must type CONFIRM to proceed.');
                }
            }]
        ]);

        // Generate draft certificate as PDF
        $pdf = Pdf::loadView('certificates.draft', ['cert' => $cert]);
        $pdfPath = 'certificates/drafts/' . $cert->id . '_draft.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // Update status to 'client_verified'
        $cert->status = 'client_verified';

        // Save preview path to the cert record
        $cert->draft_path = $pdfPath;
        $cert->save();

        // Create a new verification record
        $verification = CertVerification::create([
            'cert_id' => $cert->id,
            'verified_by' => auth()->user()->id,
            'verification_date' => now(),
            'status' => 'verified'
        ]);

        return redirect()->route('certificates.show', $cert->id)->with('success', 'Certificate has been verified and draft generated.');
    }

    public function previewDraft(Cert $cert) {
        // Check if the draft exists
        if (!$cert->draft_path || !Storage::disk('public')->exists($cert->draft_path)){
            abort(404, 'Draft not found.');
        }

        $filePath = Storage::disk('public')->path($cert->draft_path);
        return response()->file($filePath);
    }

    public function previewFinal(Cert $cert) {
        // Check if the final PDF exists
        if (!$cert->generate_pdf_path || !Storage::disk('public')->exists($cert->generate_pdf_path)){
            abort(404, 'Final certificate not found.');
        }

        $filePath = Storage::disk('public')->path($cert->generate_pdf_path);
        return response()->file($filePath);
    }

    public function downloadCertificate(Cert $cert) {

        if ($cert->generate_pdf_path && Storage::exists($cert->generate_pdf_path)) {
            return Storage::download($cert->generate_pdf_path, $cert->comp_name . '_' . $cert->cert_type . '_certificate.pdf');
        } elseif ($cert->draft_path && Storage::disk('public')->exists($cert->draft_path)) {
            return Storage::disk('public')->download($cert->draft_path, $cert->comp_ . '_' . $cert->cert_type . '_draft.pdf');
        }

        abort(404, 'Certificate not found.');
    }

    public function getVerificationLink (Cert $cert) {
        $verification = CertVerification::where('cert_id', $cert->id)->orderBy('created_at', 'desc')->first();
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

    public function verify($token) {
        $verification = CertVerification::where('token', $token)->where('expires_at', '>', now())->firstOrFail();

        $cert = Cert::findOrFail($verification->cert_id);

        $comments = CertComment::where('cert_id', $cert->id)->orderBy('created_at', 'desc')->get();
        return view('certificates.verify', [
            'cert' => $cert,
            'verification' => $verification,
            'comments' => $comments
        ]);
    }

    public function processVerification(Request $request, $token) {
        $verification = CertVerification::where('token', $token)->where('expires_at', '>', now())->firstOrFail();
        $cert = Cert::findOrFail($verification->cert_id);
        $action = $request->input('action');

        if ($action == 'verify') {
            $verification->is_verified = true;
            $verification->verified_ar = now();
            $verification->save();

            $cert->status = 'client_verified';
            $cert->save();

            if ($request->filled('comment')) {
                CertComment::create([
                    'cert_id' => $cert->id,
                    'comment' => $request->input('comment'),
                    'commented_by' => $request->input ('name', 'Client'),
                    'comment_type' => 'verification'
                ]);
            }
            return redirect()->route('certificates.verify', ['token' => $token])->with('success', 'Certificate verified successfully.');
        }

        elseif ($action == 'reject') {
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
            $cert->save();

            CertComment::create([
                'cert_id' => $cert->id,
                'comment' => $request->input('comment'),
                'commented_by' => $request->input ('name'),
                'comment_type' => 'revision_request'
            ]);
        }
    }
}