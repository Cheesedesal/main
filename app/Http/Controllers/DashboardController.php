<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $query = DocumentRequest::query();

        if ($user->role === 'user') {
            $query->where('created_by', $user->id);
        }

        return view('dashboard', [
            'user' => $user,
            'stats' => [
                'requests' => (clone $query)->count(),
                'pending' => (clone $query)->whereIn('status', ['submitted', 'under_review'])->count(),
                'ready' => (clone $query)->where('status', 'ready')->count(),
                'released' => (clone $query)->where('status', 'released')->count(),
                'users' => $user->isAdmin() ? User::count() : null,
                'activities' => $user->isStaff() ? ActivityLog::latest()->count() : null,
            ],
            'recentRequests' => (clone $query)->latest('created_at')->limit(5)->get(),
            'statusOptions' => DocumentRequest::STATUSES,
            'recentActivities' => $user->isStaff() ? ActivityLog::with('user')->latest()->limit(5)->get() : collect(),
            'recentUsers' => $user->isAdmin() ? User::latest()->limit(5)->get() : collect(),
            'statusChart' => DocumentRequest::query()
                ->when($user->role === 'user', fn ($statusQuery) => $statusQuery->where('created_by', $user->id))
                ->selectRaw('status, COUNT(*) AS total')
                ->groupBy('status')
                ->orderByDesc('total')
                ->pluck('total', 'status'),
            'monthlyChart' => $this->monthlyChart($user),
        ]);
    }

    private function monthlyChart(User $user)
    {
        $period = DB::connection()->getDriverName() === 'sqlite' ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";
        $query = DocumentRequest::query();
        if ($user->role === 'user') {
            $query->where('created_by', $user->id);
        }

        return $query->selectRaw("{$period} as period, count(*) as total")->where('created_at', '>=', now()->subMonths(5)->startOfMonth())->groupBy('period')->orderBy('period')->pluck('total', 'period');
    }
}