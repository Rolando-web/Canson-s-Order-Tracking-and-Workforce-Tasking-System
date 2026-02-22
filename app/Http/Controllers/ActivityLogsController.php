<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogsController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // User filter
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        $totalCount = ActivityLog::count();
        $paginated = $query->paginate(20);

        $iconMap = [
            'Stock In'           => 'inbox',
            'Stock Out'          => 'minus',
            'Create Order'       => 'plus',
            'Update Order'       => 'edit',
            'Complete Order'     => 'check',
            'Delete Order'       => 'trash',
            'Assign Task'        => 'user-plus',
            'Update Assignment'  => 'edit',
            'Remove Assignment'  => 'trash',
            'Dispatch Shipped'   => 'truck',
            'Dispatch Delivered'  => 'check',
            'Assign Driver'      => 'user-plus',
            'Create Employee'    => 'user-plus',
            'Update Employee'    => 'edit',
            'Delete Employee'    => 'trash',
            'Create Schedule'    => 'calendar',
            'Update Schedule'    => 'edit',
            'Delete Schedule'    => 'trash',
            'Login'              => 'login',
            'Logout'             => 'logout',
            'Update Settings'    => 'settings',
        ];

        $colorMap = [
            'Stock In'          => 'bg-emerald-500',
            'Stock Out'         => 'bg-red-500',
            'Create Order'      => 'bg-blue-500',
            'Complete Order'    => 'bg-green-500',
            'Assign Task'       => 'bg-purple-500',
            'Create Employee'   => 'bg-cyan-500',
            'Login'             => 'bg-gray-500',
            'Logout'            => 'bg-gray-400',
        ];

        $activities = $paginated->getCollection()->map(function ($log) use ($iconMap, $colorMap) {
            $userName = $log->user ? $log->user->name : 'System';
            $parts = explode(' ', $userName);
            $initial = count($parts) >= 2
                ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                : strtoupper(substr($userName, 0, 2));

            return [
                'id'          => $log->id,
                'user'        => $userName,
                'initial'     => $initial,
                'color'       => $colorMap[$log->action] ?? 'bg-gray-500',
                'action'      => $log->action,
                'description' => $log->description,
                'timestamp'   => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '',
                'icon'        => $iconMap[$log->action] ?? 'activity',
            ];
        });

        $paginated->setCollection($activities);

        return view('pages.activity-logs', [
            'activities'  => $paginated,
            'totalCount'  => $totalCount,
        ]);
    }
}
