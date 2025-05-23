<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cert;  
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CertVerification;
use App\Models\CertComment;
use App\Models\Client;
use Illuminate\Support\Str;
use App\Models\Template;

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
            'statusCounts' => $statusCounts
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
        ]);

        $data['status'] = 'pending_review'; // Default status

        $newCert = Cert::create($data);
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

        $data['last_edited_at'] = now();
        //$data['last_edited_by'] = auth()->id();

        if ($cert->status === 'need_revision') {
            if ($cert->revision_source === 'hod') {
                $data['status'] = 'pending_hod_approval';
            }  else {
            $data['status'] = 'pending_client_verification';
        }
    $data['revision_source'] = null;
}

        $cert->update($data);

        // Automatically generate the certificate PDF if status is final approved
        if ($cert->status === 'certificate_issued') {
            // Generate the PDF
            $this->generateCertificatePDF($cert); // Directly pass the Cert instance
        }
        return redirect()->route('certificates.index')->with('success', 'Certificate updated successfully.');
    }

    public function destroy(Cert $cert) {
        $cert->delete();
        return redirect()->route('certificates.index')->with('success', 'Certificate deleted successfully.');
    }

    public function show(Cert $cert) {
        // Get verification details if they exist
        $verification = CertVerification::where('cert_id', $cert->id)->latest()->first();
        $comments = CertComment::where('cert_id', $cert->id)->orderBy('created_at', 'desc')->get();

        return view('certificates.show', [
            'cert' => $cert,
            'verification' => $verification,
            'comments' => $comments
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
            'statusCounts' => $statusCounts
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

            return redirect()->route('certificates.show', $cert->id)
                ->with('success', 'Certificate approved and final PDF generated.');

        } elseif ($action === 'reject') {
            $cert->status = 'need_revision';
            $cert->revision_source = 'hod';
            $cert->hod_approved = false;
            $cert->save();

            return redirect()->route('certificates.show', $cert->id)
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
        
        return redirect()->route('certificates.show', $cert->id)->with('success', 'Verification link renewed successfully.');
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

            $cert->status = 'pending_hod_approval';
            $cert->save();

            if ($request->filled('comment')) {
                CertComment::create([
                    'cert_id' => $cert->id,
                    'comment' => $request->input('comment'),
                    'commented_by' => $request->input('name', 'Client'),
                    'comment_type' => 'verification'
                ]);
            }
            
            return redirect()->route('certificates.verify', ['token' => $token])
                ->with('success', 'Certificate verified successfully.');
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
        $pdf = new \setasign\Fpdi\Fpdi();
        $pageCount = $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx);

        // Set font and overlay your data (adjust positions as needed)
        $pdf->SetFont('Helvetica', '', 16);
        $pdf->SetTextColor(0, 0, 0);

        // Example: Place company name at (x=60, y=80)
        $pdf->SetXY(60, 80);
        $pdf->Cell(0, 10, $cert->comp_name, 0, 1);

        // Example: Place certificate type at (x=60, y=90)
        $pdf->SetXY(60, 90);
        $pdf->Cell(0, 10, $cert->cert_type . ' (' . $cert->iso_num . ')', 0, 1);

        // Example: Place issue date at (x=60, y=100)
        $pdf->SetXY(60, 100);
        $pdf->Cell(0, 10, 'Issued: ' . \Carbon\Carbon::parse($cert->issue_date)->format('d M Y'), 0, 1);

        // Example: Place expiry date at (x=60, y=110)
        $pdf->SetXY(60, 110);
        $pdf->Cell(0, 10, 'Expires: ' . \Carbon\Carbon::parse($cert->exp_date)->format('d M Y'), 0, 1);

        // Save the PDF to storage
        $pdf->Output($outputPath, 'F');

        // Save the path to the cert for preview/download
        $cert->pdf_path = $fileName;
        $cert->save();

        return $fileName;
    }
}