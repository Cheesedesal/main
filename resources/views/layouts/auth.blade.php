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
<main class="app-shell flex min-h-screen items-center justify-center px-6 py-12">
    <div class="w-full max-w-md">
        <a href="{{ auth()->check() ? route('requests.index') : route('login') }}" class="mb-8 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#215d45] font-bold text-white">D</span><span><strong class="block tracking-wide text-[#215d45]">DOCUFLOW</strong><small class="text-[#637169]">College Records Office</small></span></a>
        <section class="panel rounded-2xl p-6 sm:p-8">
            @if (session('success'))<div class="mb-5 rounded-lg border border-[#b8ddc2] bg-[#e9f8ec] px-4 py-3 text-sm text-[#21663c]">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="mb-5 rounded-lg border border-[#f0c5bd] bg-[#fff1ef] px-4 py-3 text-sm text-[#8b3029]"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </section>
    </div>
</main>
</body>
</html>