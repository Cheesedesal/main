@extends('layouts.app')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4 py-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[.2em] text-[#b26b3d]">
                {{ ucfirst($user->role) }} dashboard
            </p>
            <h1 class="serif mt-2 text-5xl font-bold">
                Good day, {{ \Illuminate\Support\Str::before($user->name, ' ') }}.
            </h1>
            <p class="mt-3 text-[#637169]">
                Here is the current state of your document workspace.
            </p>
        </div>

        <a class="button" href="{{ route('requests.create') }}">
            New request →
        </a>
    </div>

    @if ($user->isStaff())
        <section class="mt-8 grid gap-6 lg:grid-cols-2">
            <div class="panel rounded-2xl p-5">
                <h2 class="text-xl font-bold">Requests by status</h2>
                <div class="mt-4 grid gap-3">
                    @foreach (\App\Models\DocumentRequest::STATUSES as $status => $label)
                        <div><div class="mb-1 flex justify-between text-sm"><span>{{ $label }}</span><strong>{{ $statusChart[$status] ?? 0 }}</strong></div><div class="h-2 rounded-full bg-[#e7eee8]"><div class="h-2 rounded-full bg-[#28654b]" style="width: {{ $stats['requests'] ? min(100, (($statusChart[$status] ?? 0) / $stats['requests']) * 100) : 0 }}%"></div></div></div>
                    @endforeach
                </div>
            </div>
            <div class="panel rounded-2xl p-5"><h2 class="text-xl font-bold">Requests over time</h2><div class="mt-4 flex h-32 items-end gap-3">@forelse($monthlyChart as $period => $total)<div class="flex flex-1 flex-col items-center gap-2"><div class="w-full rounded-t bg-[#b26b3d]" style="height: {{ max(8, min(100, $total * 12)) }}%" title="{{ $total }} requests"></div><span class="text-xs text-[#637169]">{{ $period }}</span></div>@empty<p class="text-sm text-[#637169]">No request trend data yet.</p>@endforelse</div></div>
        </section>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Requests', $stats['requests'], 'text-[#18211f]'],
            ['Pending', $stats['pending'], 'text-[#b26b3d]'],
            ['Ready', $stats['ready'], 'text-[#28654b]'],
            ['Released', $stats['released'], 'text-[#4e3f77]'],
        ] as $stat)
            <div class="panel rounded-xl p-5">
                <p class="text-sm text-[#637169]">{{ $stat[0] }}</p>
                <p class="mt-2 text-4xl font-bold {{ $stat[2] }}">
                    {{ $stat[1] }}
                </p>
            </div>
        @endforeach

        @if ($user->isAdmin())
            <div class="panel rounded-xl p-5">
                <p class="text-sm text-[#637169]">Total users</p>
                <p class="mt-2 text-4xl font-bold text-[#215d45]">
                    {{ $stats['users'] }}
                </p>
            </div>
        @endif
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1.2fr_.8fr]">
        <section class="panel overflow-hidden rounded-2xl">
            <div class="flex items-center justify-between border-b border-[#dfe7df] px-5 py-4">
                <h2 class="text-xl font-bold">Recent requests</h2>
                <a class="text-sm font-semibold text-[#215d45]" href="{{ route('requests.index') }}">
                    View all
                </a>
            </div>

            @forelse ($recentRequests as $request)
                <div class="flex items-center justify-between gap-4 border-b border-[#edf1ed] px-5 py-4">
                    <div>
                        <p class="font-semibold">{{ $request->document_type }}</p>
                        <p class="text-sm text-[#637169]">
                            {{ $request->tracking_code }} · {{ $request->requester_name }}
                        </p>
                    </div>
                    <span class="status status-{{ $request->status }}">
                        {{ $statusOptions[$request->status] ?? ucwords(str_replace('_', ' ', $request->status)) }}
                    </span>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-[#637169]">
                    No requests yet.
                </div>
            @endforelse
        </section>

        <section class="panel rounded-2xl p-5">
            <h2 class="text-xl font-bold">Quick links</h2>
            <div class="mt-4 grid gap-3">
                <a class="rounded-lg border border-[#dfe7df] p-3 font-semibold text-[#215d45]" href="{{ route('requests.create') }}">
                    Submit a document request
                </a>
                <a class="rounded-lg border border-[#dfe7df] p-3 font-semibold text-[#215d45]" href="{{ route('profile.edit') }}">
                    Manage my account
                </a>

                @if ($user->isAdmin())
                    <a class="rounded-lg border border-[#dfe7df] p-3 font-semibold text-[#215d45]" href="{{ route('admin.users') }}">
                        Manage users and roles
                    </a>
                    <a class="rounded-lg border border-[#dfe7df] p-3 font-semibold text-[#215d45]" href="{{ route('reports.index') }}">
                        Open reports
                    </a>
                @endif
            </div>
        </section>
    </div>

    @if ($user->isStaff())
        <section class="panel mt-6 overflow-hidden rounded-2xl">
            <div class="border-b border-[#dfe7df] px-5 py-4">
                <h2 class="text-xl font-bold">Recent activity</h2>
            </div>

            @forelse ($recentActivities as $activity)
                <div class="flex justify-between gap-4 border-b border-[#edf1ed] px-5 py-3 text-sm">
                    <span>{{ $activity->description }}</span>
                    <time class="shrink-0 text-[#637169]">
                        {{ $activity->created_at->diffForHumans() }}
                    </time>
                </div>
            @empty
                <div class="px-5 py-8 text-[#637169]">
                    No activity recorded.
                </div>
            @endforelse
        </section>
    @endif

    @if ($user->isAdmin())
        <section class="panel mt-6 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Recent users</h2>
                <a class="text-sm font-semibold text-[#215d45]" href="{{ route('admin.users') }}">
                    Manage users
                </a>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($recentUsers as $recentUser)
                    <div class="rounded-lg border border-[#dfe7df] p-3">
                        <strong>{{ $recentUser->name }}</strong>
                        <p class="text-sm text-[#637169]">
                            {{ $recentUser->email }} · {{ ucfirst($recentUser->role) }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
