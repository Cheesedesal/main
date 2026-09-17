<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $activities = ActivityLog::with('user')->when($request->search, fn ($query, $search) => $query->where('description', 'like', "%{$search}%"))->when($request->user_id, fn ($query, $userId) => $query->where('user_id', $userId))->when($request->action, fn ($query, $action) => $query->where('action', $action))->when($request->from, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))->when($request->to, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))->latest()->paginate(20)->withQueryString();

        return view('admin.activities.index', ['activities' => $activities, 'users' => User::orderBy('name')->get(), 'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action')]);
    }
}