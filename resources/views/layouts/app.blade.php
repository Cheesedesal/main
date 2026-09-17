<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'DocuFlow' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
    @endif
</head>
<body>
<div class="app-shell min-h-screen">
    <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#215d45] font-bold text-white">D</span>
            <span><strong class="block tracking-wide text-[#215d45]">DOCUFLOW</strong><small class="text-[#637169]">College Records Office</small></span>
        </a>
        <nav class="flex items-center gap-3 text-sm">
            <a class="hidden font-semibold text-[#215d45] sm:inline" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="hidden font-semibold text-[#215d45] sm:inline" href="{{ route('requests.index') }}">Requests</a>
            <a class="font-semibold text-[#215d45]" href="{{ route('notifications.index') }}">
                Notifications
                @if (auth()->user()->unreadNotifications()->count())
                    <span class="ml-1 rounded-full bg-[#b26b3d] px-1.5 py-0.5 text-xs text-white">{{ auth()->user()->unreadNotifications()->count() }}</span>
                @endif
            </a>
            @if (auth()->user()->isAdmin())
                <a class="hidden font-semibold text-[#215d45] lg:inline" href="{{ route('admin.users') }}">Users</a>
                <a class="hidden font-semibold text-[#215d45] lg:inline" href="{{ route('reports.index') }}">Reports</a>
                <a class="hidden font-semibold text-[#215d45] lg:inline" href="{{ route('admin.activities') }}">Activity</a>
                <a class="hidden font-semibold text-[#215d45] lg:inline" href="{{ route('admin.settings') }}">Settings</a>
            @elseif (auth()->user()->isStaff())
                <a class="hidden font-semibold text-[#215d45] lg:inline" href="{{ route('staff.reports') }}">Reports</a>
                <a class="hidden font-semibold text-[#215d45] lg:inline" href="{{ route('staff.activities') }}">Activity</a>
            @endif
            <a class="font-semibold text-[#215d45]" href="{{ route('profile.edit') }}">{{ auth()->user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-full border border-[#cbd8cd] px-3 py-1 text-xs font-semibold text-[#637169]" type="submit">Log out</button>
            </form>
        </nav>
    </header>
    <main class="mx-auto max-w-7xl px-6 pb-16 lg:px-10">
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-[#b8ddc2] bg-[#e9f8ec] px-4 py-3 text-sm text-[#21663c]">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-[#f0c5bd] bg-[#fff1ef] px-4 py-3 text-sm text-[#8b3029]">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
