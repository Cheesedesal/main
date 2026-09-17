@component('layouts.auth', ['title' => 'Log in | DocuFlow'])
    <h1 class="serif text-4xl font-bold">Welcome back</h1><p class="mt-2 text-sm text-[#637169]">Sign in to manage your document requests.</p>
    <form method="POST" action="{{ route('login') }}" class="mt-6 grid gap-4">@csrf
        <label class="grid gap-1 text-sm font-semibold">Email address<input class="input" type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        <label class="grid gap-1 text-sm font-semibold">Password<input class="input" type="password" name="password" required></label>
        <label class="flex items-center gap-2 text-sm text-[#637169]"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button class="button" type="submit">Log in</button>
    </form>
    <div class="mt-6 flex justify-between text-sm"><a class="font-semibold text-[#215d45]" href="{{ route('password.request') }}">Forgot password?</a><a class="font-semibold text-[#215d45]" href="{{ route('register') }}">Create account</a></div>
@endcomponent