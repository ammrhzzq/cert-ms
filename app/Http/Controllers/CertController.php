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
            'exp_date' => 'required|date',
            'status' => 'required|string',
        ]);

        $data['last_edited_at'] = now();
        //$data['last_edited_by'] = auth()->id();

        $cert->update($data);

        // Automatically generate the certificate PDF if status is final approved
        if ($cert->status === 'final_approved') {
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
            $cert->save();

            CertComment::create([
                'cert_id' => $cert->id,
                'comment' => $request->input('comment'),
                'commented_by' => $request->input('name'),
                'comment_type' => 'revision_request'
            ]);
        }
    }
    // New method to fetch client data for Ajax requests
    public function getClientData($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client);
    }
}