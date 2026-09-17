@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-2xl py-8">
	<a class="text-sm font-semibold text-[#215d45]" href="{{ route('requests.index') }}">← Back to requests</a>
	<h1 class="serif mt-4 text-5xl font-bold">New document request</h1>
	<p class="mt-3 text-[#637169]">Submit an official college document request.</p>
	<form class="panel mt-8 grid gap-4 rounded-2xl p-6" method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data">
		@csrf
		<label class="grid gap-1 text-sm font-semibold">Requester name<input class="input" name="requester_name" value="{{ old('requester_name', auth()->user()->name) }}" required></label>
		<div class="grid gap-4 sm:grid-cols-2"><label class="grid gap-1 text-sm font-semibold">Email<input class="input" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></label><label class="grid gap-1 text-sm font-semibold">Student ID<input class="input" name="student_id" value="{{ old('student_id') }}"></label></div>
		<div class="grid gap-4 sm:grid-cols-[1.5fr_.5fr]"><label class="grid gap-1 text-sm font-semibold">Document type<select class="input" name="document_type" required><option value="">Choose a type</option><option>Certificate of Enrollment</option><option>Transcript of Records</option><option>Good Moral Certificate</option><option>Certificate of Graduation</option><option>Other official document</option></select></label><label class="grid gap-1 text-sm font-semibold">Copies<input class="input" type="number" name="copies" value="{{ old('copies', 1) }}" min="1" max="10" required></label></div>
		<label class="grid gap-1 text-sm font-semibold">Purpose<input class="input" name="purpose" value="{{ old('purpose') }}" required></label>
		<label class="grid gap-1 text-sm font-semibold">Supporting documents<input class="input" type="file" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"><span class="text-xs font-normal text-[#637169]">Up to 5 files, 5 MB each.</span></label>
		<button class="button mt-2" type="submit">Submit request</button>
	</form>
</div>
@endsection
