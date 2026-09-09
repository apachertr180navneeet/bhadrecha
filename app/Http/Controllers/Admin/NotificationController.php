<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Services\ExpiryNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display all notifications with filters and pagination
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Run sync check so users always see current alerts
        ExpiryNotificationService::checkAndGenerateNotifications(1);

        $type = $request->get('type', 'all');
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        // Base query for notifications of current user
        $query = DB::table('notifications')
            ->join('notification_users', 'notifications.id', '=', 'notification_users.notification_id')
            ->where('notification_users.user_id', $user->id)
            ->whereNull('notifications.deleted_at')
            ->select(
                'notifications.id',
                'notifications.notification_type',
                'notifications.action_type',
                'notifications.title',
                'notifications.description',
                'notifications.url',
                'notifications.data',
                'notifications.created_at',
                'notification_users.read_at',
                'notification_users.id as pivot_id'
            );

        // Filter by notification type
        if ($type && $type !== 'all') {
            if ($type === 'eway') {
                $query->where('notifications.notification_type', 'eway_bill_expiry');
            } elseif ($type === 'vehicle') {
                $query->where('notifications.notification_type', 'vehicle_document_expiry');
            } elseif ($type === 'driver') {
                $query->where('notifications.notification_type', 'driver_document_expiry');
            } elseif ($type === 'dms') {
                $query->where('notifications.notification_type', 'document_expiry');
            } else {
                $query->where('notifications.notification_type', $type);
            }
        }

        // Filter by read/unread status
        if ($status === 'unread') {
            $query->whereNull('notification_users.read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('notification_users.read_at');
        }

        // Filter by search text
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('notifications.title', 'like', "%{$search}%")
                  ->orWhere('notifications.description', 'like', "%{$search}%");
            });
        }

        $dateSort = $request->get('date_sort', 'latest');
        $notifications = $query->orderBy('notifications.created_at', $dateSort === 'oldest' ? 'asc' : 'desc')->paginate(15)->withQueryString();

        // Count metrics for tabs/cards
        $counts = [
            'all' => DB::table('notification_users')->where('user_id', $user->id)->count(),
            'unread' => DB::table('notification_users')->where('user_id', $user->id)->whereNull('read_at')->count(),
            'eway' => DB::table('notifications')
                ->join('notification_users', 'notifications.id', '=', 'notification_users.notification_id')
                ->where('notification_users.user_id', $user->id)
                ->where('notifications.notification_type', 'eway_bill_expiry')
                ->whereNull('notifications.deleted_at')
                ->count(),
            'vehicle' => DB::table('notifications')
                ->join('notification_users', 'notifications.id', '=', 'notification_users.notification_id')
                ->where('notification_users.user_id', $user->id)
                ->where('notifications.notification_type', 'vehicle_document_expiry')
                ->whereNull('notifications.deleted_at')
                ->count(),
            'driver' => DB::table('notifications')
                ->join('notification_users', 'notifications.id', '=', 'notification_users.notification_id')
                ->where('notification_users.user_id', $user->id)
                ->where('notifications.notification_type', 'driver_document_expiry')
                ->whereNull('notifications.deleted_at')
                ->count(),
            'dms' => DB::table('notifications')
                ->join('notification_users', 'notifications.id', '=', 'notification_users.notification_id')
                ->where('notification_users.user_id', $user->id)
                ->where('notifications.notification_type', 'document_expiry')
                ->whereNull('notifications.deleted_at')
                ->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'counts', 'type', 'status', 'search'));
    }

    /**
     * AJAX endpoint to fetch unread notifications for navbar dropdown
     */
    public function getUnread(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $unreadCount = NotificationUser::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $notifications = DB::table('notifications')
            ->join('notification_users', 'notifications.id', '=', 'notification_users.notification_id')
            ->where('notification_users.user_id', $user->id)
            ->whereNull('notifications.deleted_at')
            ->select(
                'notifications.id',
                'notifications.notification_type',
                'notifications.title',
                'notifications.description',
                'notifications.url',
                'notifications.data',
                'notifications.created_at',
                'notification_users.read_at'
            )
            ->orderBy('notifications.created_at', 'desc')
            ->take(8)
            ->get()
            ->map(function ($item) {
                $item->data = json_decode($item->data, true) ?: [];
                $item->time_ago = Carbon::parse($item->created_at)->diffForHumans();
                return $item;
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notifUser = NotificationUser::where('notification_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($notifUser && is_null($notifUser->read_at)) {
            $notifUser->read_at = Carbon::now();
            $notifUser->save();
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        $notification = Notification::find($id);
        if ($notification && !empty($notification->url)) {
            return redirect($notification->url);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications for current user as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        NotificationUser::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Manually trigger scan check
     */
    public function syncCheck()
    {
        $results = ExpiryNotificationService::checkAndGenerateNotifications(1);
        $total = $results['total_created'];

        return redirect()->back()->with('success', "Scan completed. Found {$total} new expiring alert(s).");
    }
}
