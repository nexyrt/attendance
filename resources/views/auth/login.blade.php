<x-guest-layout>
    <div class="my-6 flex items-center justify-center">
        <img class="w-16" src="{{ asset('/assets/images/kisantra-logo.png') }}" />
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="space-y-4">
            <x-input label="Email *" type="email" name="email" placeholder="admin@gmail.com" required autofocus
                autocomplete="username" />

            <x-password label="Password *" type="password" name="password" required autocomplete="current-password"
                placeholder="********" />
        </div>

        <div class="block mt-4">
            <x-checkbox label="Remember me" id="remember_me" type="checkbox" name="remember" />
        </div>

        <div class="flex items-center justify-end mt-4">

            <x-button type="submit" class="ms-3">
                {{ __('Log in') }}
            </x-button>
        </div>
    </form>
</x-guest-layout>
