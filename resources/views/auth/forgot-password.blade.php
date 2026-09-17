@component('layouts.auth', ['title' => 'Reset password | DocuFlow'])
    <h1 class="serif text-4xl font-bold">Reset your password</h1><p class="mt-2 text-sm text-[#637169]">Enter your email and we will send a reset link. In local development, it appears in the Laravel log.</p>
    <form method="POST" action="{{ route('password.email') }}" class="mt-6 grid gap-4">@csrf<label class="grid gap-1 text-sm font-semibold">Email address<input class="input" type="email" name="email" value="{{ old('email') }}" required autofocus></label><button class="button" type="submit">Email reset link</button></form>
    <p class="mt-6 text-center text-sm"><a class="font-semibold text-[#215d45]" href="{{ route('login') }}">Back to login</a></p>
@endcomponent