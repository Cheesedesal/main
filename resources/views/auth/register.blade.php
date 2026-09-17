@component('layouts.auth', ['title' => 'Create account | DocuFlow'])
    <h1 class="serif text-4xl font-bold">Create your account</h1><p class="mt-2 text-sm text-[#637169]">Use your account to submit and track college documents.</p>
    <form method="POST" action="{{ route('register') }}" class="mt-6 grid gap-4">@csrf
        <label class="grid gap-1 text-sm font-semibold">Full name<input class="input" name="name" value="{{ old('name') }}" required autofocus></label>
        <label class="grid gap-1 text-sm font-semibold">Email address<input class="input" type="email" name="email" value="{{ old('email') }}" required></label>
        <label class="grid gap-1 text-sm font-semibold">Password<input class="input" type="password" name="password" required minlength="8"></label>
        <label class="grid gap-1 text-sm font-semibold">Confirm password<input class="input" type="password" name="password_confirmation" required minlength="8"></label>
        <button class="button" type="submit">Create account</button>
    </form>
    <p class="mt-6 text-center text-sm text-[#637169]">Already registered? <a class="font-semibold text-[#215d45]" href="{{ route('login') }}">Log in</a></p>
@endcomponent