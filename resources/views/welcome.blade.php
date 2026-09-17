<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocuFlow | College Records Office</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            :root { font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif; color: #18211f; background: #f2f5f0; }
            body { margin: 0; }
            .serif { font-family: Georgia, 'Times New Roman', serif; }
            .app-shell { min-height: 100vh; background: radial-gradient(circle at 85% 0%, #dbeade 0, transparent 34rem), #f2f5f0; }
            .panel { background: rgba(255, 255, 255, .82); border: 1px solid #dfe7df; box-shadow: 0 18px 50px rgba(37, 61, 48, .08); }
            .input { width: 100%; border: 1px solid #cbd8cd; background: #fbfdfb; padding: .72rem .8rem; border-radius: .55rem; outline: none; }
            .input:focus { border-color: #28654b; box-shadow: 0 0 0 3px rgba(40, 101, 75, .12); }
            .button { border: 0; border-radius: .55rem; background: #215d45; color: white; padding: .75rem 1rem; font-weight: 700; cursor: pointer; }
            .button:hover { background: #174b37; }
            .status { border-radius: 999px; padding: .28rem .6rem; font-size: .72rem; font-weight: 700; }
            .status-submitted { color: #795a16; background: #fff1c8; }
            .status-under_review { color: #175c77; background: #d9f0fa; }
            .status-ready { color: #21663c; background: #d9f2df; }
            .status-released { color: #4e3f77; background: #e9e1fa; }
            .status-declined { color: #8b3029; background: #fbe0dc; }
        </style>
    @endif
</head>
<body>
<div class="app-shell">
    <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-7 lg:px-10">
        <div class="flex items-center gap-3"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#215d45] text-lg font-bold text-white">D</div><div><p class="text-sm font-bold tracking-wide text-[#215d45]">DOCUFLOW</p><p class="text-xs text-[#637169]">College Records Office</p></div></div>
        <nav class="flex items-center gap-3 text-sm">
            <span class="hidden text-[#637169] sm:inline">{{ auth()->user()->name }}</span>
            <a class="font-semibold text-[#215d45]" href="{{ route('profile.edit') }}">My account</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-full border border-[#cbd8cd] px-3 py-1 text-xs font-semibold text-[#637169]" type="submit">Log out</button></form>
        </nav>
    </header>
    <main class="mx-auto max-w-7xl px-6 pb-16 lg:px-10">
        @if (session('success'))<div class="mb-6 rounded-xl border border-[#b8ddc2] bg-[#e9f8ec] px-4 py-3 text-sm font-semibold text-[#21663c]">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="mb-6 rounded-xl border border-[#f0c5bd] bg-[#fff1ef] px-4 py-3 text-sm text-[#8b3029]">Please check the highlighted request details and try again.</div>@endif
        <section class="grid gap-10 pb-12 pt-8 lg:grid-cols-[1.15fr_.85fr] lg:items-end">
            <div><p class="mb-4 text-sm font-bold uppercase tracking-[.2em] text-[#b26b3d]">Request intelligence, made practical</p><h1 class="serif max-w-3xl text-5xl leading-[.98] tracking-tight text-[#18211f] lg:text-7xl">Every document request, <em class="text-[#28654b]">accounted for.</em></h1><p class="mt-6 max-w-xl text-lg leading-8 text-[#637169]">A calm, shared workspace for students and the Records Office to submit, review, and release official college documents.</p></div>
            <div class="panel rounded-2xl p-5"><div class="mb-4 flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-[#637169]">New request</p><h2 class="mt-1 text-xl font-bold">Start a document request</h2></div><span class="text-2xl">↗</span></div>
                <form action="{{ route('requests.store') }}" method="POST" class="grid gap-3">@csrf
                    <input class="input" name="requester_name" value="{{ old('requester_name') }}" placeholder="Student or requester name" required>
                    <div class="grid gap-3 sm:grid-cols-2"><input class="input" type="email" name="email" value="{{ old('email') }}" placeholder="Email address" required><input class="input" name="student_id" value="{{ old('student_id') }}" placeholder="Student ID (optional)"></div>
                    <div class="grid gap-3 sm:grid-cols-[1.4fr_.6fr]"><select class="input" name="document_type" required><option value="">Document type</option><option>Certificate of Enrollment</option><option>Transcript of Records</option><option>Good Moral Certificate</option><option>Certificate of Graduation</option><option>Other official document</option></select><input class="input" type="number" name="copies" value="{{ old('copies', 1) }}" min="1" max="10" placeholder="Copies" required></div>
                    <input class="input" name="purpose" value="{{ old('purpose') }}" placeholder="Purpose of request" required>
                    <button class="button mt-1" type="submit">Submit request <span class="ml-2">→</span></button>
                </form>
            </div>
        </section>
        <section class="grid grid-cols-2 gap-3 pb-10 lg:grid-cols-4">
            @foreach ([['label' => 'Total requests', 'value' => $metrics['total'], 'tone' => 'text-[#18211f]'], ['label' => 'Needs attention', 'value' => $metrics['active'], 'tone' => 'text-[#b26b3d]'], ['label' => 'Ready for pickup', 'value' => $metrics['ready'], 'tone' => 'text-[#28654b]'], ['label' => 'Released', 'value' => $metrics['released'], 'tone' => 'text-[#4e3f77]']] as $metric)<div class="rounded-xl border border-[#dfe7df] bg-white/60 p-4"><p class="text-xs font-semibold text-[#637169]">{{ $metric['label'] }}</p><p class="mt-2 text-3xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p></div>@endforeach
        </section>
        <section class="panel overflow-hidden rounded-2xl"><div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#dfe7df] px-5 py-5"><div><p class="text-xs font-bold uppercase tracking-widest text-[#637169]">Live queue</p><h2 class="mt-1 text-2xl font-bold">Document requests</h2></div><span class="text-sm text-[#637169]">{{ $requests->count() }} records</span></div>
            @if ($requests->isEmpty())<div class="px-5 py-14 text-center text-[#637169]">No requests yet. New submissions will appear here.</div>@else<div class="divide-y divide-[#e7ede8]">@foreach ($requests as $documentRequest)<div class="grid gap-4 px-5 py-5 lg:grid-cols-[1.2fr_1fr_.8fr_1fr] lg:items-center"><div><div class="flex flex-wrap items-center gap-2"><strong>{{ $documentRequest->requester_name }}</strong><span class="text-xs font-bold text-[#b26b3d]">{{ $documentRequest->tracking_code }}</span></div><p class="mt-1 text-sm text-[#637169]">{{ $documentRequest->document_type }} · {{ $documentRequest->copies }} {{ Str::plural('copy', $documentRequest->copies) }}</p></div><div><p class="text-xs font-bold uppercase tracking-wider text-[#91a098]">Purpose</p><p class="mt-1 text-sm">{{ $documentRequest->purpose }}</p></div><div><span class="status status-{{ $documentRequest->status }}">{{ $statusOptions[$documentRequest->status] }}</span><p class="mt-2 text-xs text-[#91a098]">{{ $documentRequest->created_at->diffForHumans() }}</p></div><form action="{{ route('requests.status', $documentRequest) }}" method="POST" class="flex gap-2">@csrf @method('PATCH')<select class="input !w-auto flex-1 py-2 text-sm" name="status" aria-label="Update status for {{ $documentRequest->tracking_code }}">@foreach ($statusOptions as $value => $label)<option value="{{ $value }}" @selected($documentRequest->status === $value)>{{ $label }}</option>@endforeach</select><button class="button py-2 text-sm" title="Save status" type="submit">Save</button></form></div>@endforeach</div>@endif
        </section>
    </main>
</div>
</body>
</html>
