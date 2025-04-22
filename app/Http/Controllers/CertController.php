<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cert;   

class CertController extends Controller
{
    public function index(Request $request) {
        $query = Cert::query();
        
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
        
        return view('certificates.index', [
            'certs' => $cert,
            'certTypes' => $certTypes,
            'isoNumbers' => $isoNumbers,
            'companyNames' => $companyNames,
            'regDates' => $regDates,
            'issueDates' => $issueDates,
            'expDates' => $expDates
        ]);
    }
    
    public function create() {
        return view('certificates.create');
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
            'reg_date' => 'required|date',
            'issue_date' => 'required|date',
            'exp_date' => 'required|date',
        ]);

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
            'reg_date' => 'required|date',
            'issue_date' => 'required|date',
            'exp_date' => 'required|date',
        ]);

        $cert->update($data);
        return redirect()->route('certificates.index')->with('success', 'Certificate updated successfully.');
    }

    public function destroy(Cert $cert) {
        $cert->delete();
        return redirect()->route('certificates.index')->with('success', 'Certificate deleted successfully.');
    }

}
