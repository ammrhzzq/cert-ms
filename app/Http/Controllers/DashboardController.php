<?php

namespace App\Http\Controllers;

use App\Models\Cert;
use App\Models\Client;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Count certificates by status
        $pendingReview = Cert::where('status', 'pending_review')->count();
        $clientVerified = Cert::where('status', 'client_verified')->count();
        $needRevision = Cert::where('status', 'need_revision')->count();
        $pendingHodApproval = Cert::where('status', 'pending_hod_approval')->count();
        $certificateIssued = Cert::where('status', 'certificate_issued')->count();
        
        // Count total clients
        $clientCount = Client::count();
        
        return view('dashboard.index', [
            'pendingReview' => $pendingReview,
            'clientVerified' => $clientVerified,
            'needRevision' => $needRevision,
            'pendingHodApproval' => $pendingHodApproval,
            'certificateIssued' => $certificateIssued,
            'clientCount' => $clientCount
        ]);
    }
}