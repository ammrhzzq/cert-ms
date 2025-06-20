<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cert;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $user = Auth::user();
        $notifications = [];

        // Get pending actions based on user role
        if (in_array($user->role, ['staff', 'manager', 'hod'])) {
            // Certificates pending review
            $pendingReview = Cert::where('status', 'pending_review')->count();
            if ($pendingReview > 0) {
                $notifications[] = [
                    'id' => 'pending_review',
                    'title' => 'Certificates Pending Review',
                    'message' => "{$pendingReview} certificate(s) require your review",
                    'type' => 'warning',
                    'count' => $pendingReview,
                    'action_url' => route('certificates.index', ['status' => 'pending_review'])
                ];
            }
        }

        // Get pending actions based on user role
        if (in_array($user->role, ['manager', 'hod'])) {
            // Client verified certificates
            $clientVerified = Cert::where('status', 'client_verified')->count();
            if ($clientVerified > 0) {
                $notifications[] = [
                    'id' => 'client_verified',
                    'title' => 'Certificates Ready for Number Assignment',
                    'message' => "{$clientVerified} certificate(s) verified by client",
                    'type' => 'success',
                    'count' => $clientVerified,
                    'action_url' => route('certificates.index', ['status' => 'client_verified'])
                ];
            }

            // Certificates needing revision
            $needRevision = Cert::where('status', 'need_revision')->count();
            if ($needRevision > 0) {
                $notifications[] = [
                    'id' => 'need_revision',
                    'title' => 'Certificates Need Revision',
                    'message' => "{$needRevision} certificate(s) require updates",
                    'type' => 'danger',
                    'count' => $needRevision,
                    'action_url' => route('certificates.index', ['status' => 'need_revision'])
                ];
            }
        }

        if ($user->role === 'hod') {
            // HOD specific notifications
            $pendingHodApproval = Cert::where('status', 'pending_hod_approval')->count();
            if ($pendingHodApproval > 0) {
                $notifications[] = [
                    'id' => 'pending_hod_approval',
                    'title' => 'Pending Your Approval',
                    'message' => "{$pendingHodApproval} certificate(s) awaiting HOD approval",
                    'type' => 'warning',
                    'count' => $pendingHodApproval,
                    'action_url' => route('certificates.index', ['status' => 'pending_hod_approval'])
                ];
            }
        }

        if ($user->role === 'staff') {
            // Certificates needing revision
            $needRevision = Cert::where('status', 'need_revision')->count();
            if ($needRevision > 0) {
                $notifications[] = [
                    'id' => 'need_revision',
                    'title' => 'Certificates Need Revision',
                    'message' => "{$needRevision} certificate(s) require updates",
                    'type' => 'danger',
                    'count' => $needRevision,
                    'action_url' => route('certificates.index', ['status' => 'need_revision'])
                ];
            }

            // Awaiting client verification
            $verificationCount = Cert::where('status', 'pending_client_verification')->count();
            if ($verificationCount > 0) {
                $notifications[] = [
                    'id' => 'my_verifications',
                    'title' => 'Awaiting Client Verification',
                    'message' => "{$verificationCount} certificate(s) sent to client",
                    'type' => 'info',
                    'count' => $verificationCount,
                    'action_url' => route('certificates.index', ['my_certificates' => true, 'status' => 'pending_client_verification'])
                ];
            }
        }

        // Calculate total notification count
        $totalCount = array_sum(array_column($notifications, 'count'));

        return response()->json([
            'notifications' => $notifications,
            'total_count' => $totalCount
        ]);
    }
}
