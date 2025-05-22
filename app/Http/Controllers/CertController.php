<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cert;
use App\Models\Client;

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
        return redirect()->route('certificates.index')->with('success', 'Certificate updated successfully.');
    }

    public function destroy(Cert $cert)
    {
        $cert->delete();
        return redirect()->route('certificates.index')->with('success', 'Certificate deleted successfully.');
    }

    public function preview(Cert $cert)
    {
        return view('certificates.preview', ['cert' => $cert]);
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

    // New method to fetch client data for Ajax requests
    public function getClientData($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client);
    }
}