<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="tallstackui_darkTheme()">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('/assets/images/jkb.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

        <tallstackui:script />
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased" x-cloak x-data="{ name: @js(auth()->user()->name) }"
        x-on:name-updated.window="name = $event.detail.name"
        x-bind:class="{ 'dark bg-gray-800': darkTheme, 'bg-gray-100': !darkTheme }">
        <x-layout>
            <x-slot:top>
                <x-dialog />
                <x-toast />
            </x-slot:top>
            <x-slot:header>
                <x-layout.header>
                    <x-slot:left>
                        <x-theme-switch />
                    </x-slot:left>
                    <x-slot:right>
                        <x-dropdown>
                            <x-slot:action>
                                <div>
                                    <button class="cursor-pointer" x-on:click="show = !show">
                                        <span class="text-base font-semibold text-primary-500" x-text="name"></span>
                                    </button>
                                </div>
                            </x-slot:action>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown.items :text="__('Profile')" :href="route('user.profile')" />
                                <x-dropdown.items :text="__('Logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();" separator />
                            </form>
                        </x-dropdown>
                    </x-slot:right>
                </x-layout.header>
            </x-slot:header>
            <x-slot:menu>
                <x-side-bar smart collapsible>
                    <x-slot:brand>
                        <div class="mt-8 flex items-center justify-center">
                            <img src="{{ asset('/assets/images/jkb.png') }}" width="40" height="40" />
                        </div>
                    </x-slot:brand>

                    {{-- All Roles --}}
                    <x-side-bar.item text="Dashboard" icon="home" :route="route('dashboard')" wire:navigate />
                    <x-side-bar.item text="Check In/Out" icon="cursor-arrow-rays" :route="route('attendance.check-in')" wire:navigate />

                    {{-- Staff Only --}}
                    @if (auth()->user()->role === 'staff')
                        <x-side-bar.item text="My Attendance" icon="clock" :route="route('attendance.index')" wire:navigate />
                        <x-side-bar.item text="My Leaves" icon="calendar-days" :route="route('leave-requests.index')" wire:navigate />
                    @endif

                    {{-- Manager Only --}}
                    @if (auth()->user()->role === 'manager')
                        <x-side-bar.separator text="Management" />
                        <x-side-bar.item text="Team Attendance" icon="users" :route="route('manager.team-attendance')" wire:navigate />
                        <x-side-bar.item text="Leave Approvals" icon="document-check" :route="route('manager.leave-requests.index')" wire:navigate />
                    @endif

                    {{-- Director Only --}}
                    @if (auth()->user()->role === 'director')
                        <x-side-bar.separator text="Executive" />
                        <x-side-bar.item text="Final Leave Approval" icon="shield-check" :route="route('director.leave-requests.index')"
                            wire:navigate />
                    @endif

                    {{-- Admin, Direktur & Manager --}}
                    @if (in_array(auth()->user()->role, ['admin', 'director', 'manager']))
                        <x-side-bar.separator text="HR Management" />
                        <x-side-bar.item text="Users" icon="user-group" :route="route('users.index')" wire:navigate />
                        <x-side-bar.item text="Schedule" icon="clock" :route="route('schedule.index')" wire:navigate />

                        @if (in_array(auth()->user()->role, ['admin']))
                            <x-side-bar.item text="HR Leave Approval" icon="document-check" :route="route('admin.leave-requests.index')"
                                wire:navigate />
                        @endif

                        <x-side-bar.separator text="Strategic" />
                        <x-side-bar.item text="Office Management" icon="map-pin" :route="route('office-management.index')" wire:navigate />
                    @endif

                </x-side-bar>
            </x-slot:menu>
            {{ $slot }}
        </x-layout>
        @livewireScripts
        @stack('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    </body>

</html>
