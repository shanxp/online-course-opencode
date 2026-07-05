<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_ids')) {
            $ids = array_filter(explode(',', $request->user_ids));
            if (!empty($ids)) {
                $query->whereIn('user_id', $ids);
            }
        }

        $logs = $query->paginate(50)->withQueryString();

        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        $users = User::orderBy('name')->get(['id', 'name']);

        $selectedUsers = collect();
        if ($request->filled('user_ids')) {
            $ids = array_filter(explode(',', $request->user_ids));
            $selectedUsers = User::whereIn('id', $ids)->get(['id', 'name']);
        }

        return view('admin.activity-logs.index', compact('logs', 'actions', 'users', 'selectedUsers'));
    }
}
