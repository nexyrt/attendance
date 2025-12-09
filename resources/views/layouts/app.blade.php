<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="tallstackui_darkTheme()">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('/assets/images/kisantra-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <tallstackui:script />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased" x-cloak x-data="{ name: @js(auth()->user()->name) }" x-on:name-updated.window="name = $event.detail.name"
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
                        <img src="{{ asset('/assets/images/kisantra-logo.png') }}" width="40" height="40" />
                    </div>
                </x-slot:brand>

                {{-- Global Menu --}}
                @can('dashboard.view')
                    @php
                        $dashboardRoute = match (true) {
                            auth()->user()->hasRole('director') => route('dashboard.director'),
                            auth()->user()->hasRole('admin') => route('dashboard.admin'),
                            auth()->user()->hasRole('manager') => route('dashboard.manager'),
                            default => route('dashboard.staff'),
                        };
                    @endphp
                    <x-side-bar.item text="Dashboard" icon="home" :route="$dashboardRoute" wire:navigate />
                @endcan

                @can('attendance.check-in')
                    <x-side-bar.item text="Check In/Out" icon="cursor-arrow-rays" :route="route('attendance.check-in')" wire:navigate />
                @endcan

                {{-- My Workspace - Staff --}}
                @if (auth()->user()->hasAnyPermission(['attendance.view-own', 'leave-requests.view-own']))
                    <x-side-bar.separator text="My Workspace" />

                    @can('attendance.view-own')
                        <x-side-bar.item text="My Attendance" icon="clock" :route="route('attendance.my')" wire:navigate />
                    @endcan

                    @can('leave-requests.view-own')
                        <x-side-bar.item text="My Leaves" icon="calendar-days" :route="route('leave-requests.my.index')" wire:navigate />
                    @endcan
                @endif

                {{-- Team Management - Manager --}}
                @can('attendance.view-team')
                    <x-side-bar.separator text="Team Management" />
                    <x-side-bar.item text="Team Attendance" icon="users" :route="route('attendance.team')" wire:navigate />
                @endcan

                {{-- HR Management - Admin/Manager/Director --}}
                @if (auth()->user()->hasAnyPermission(['attendance.view-all', 'leave-requests.view-pending']))
                    <x-side-bar.separator text="HR Management" />

                    @can('attendance.view-all')
                        <x-side-bar.item text="All Attendance" icon="clipboard-document-list" :route="route('attendance.all')"
                            wire:navigate />
                    @endcan

                    @can('leave-requests.view-pending')
                        <x-side-bar.item text="Leave Approvals" icon="document-check" :route="route('leave-requests.approvals.index')" wire:navigate />
                    @endcan
                @endif

                {{-- System Management --}}
                @if (auth()->user()->hasAnyPermission(['users.view', 'schedule.view', 'office-locations.view']))
                    <x-side-bar.separator text="System Management" />

                    @can('users.view')
                        <x-side-bar.item text="Users" icon="user-group" :route="route('users.index')" wire:navigate />
                    @endcan

                    @can('schedule.view')
                        <x-side-bar.item text="Schedule" icon="clock" :route="route('schedule.index')" wire:navigate />
                    @endcan

                    @can('office-locations.view')
                        <x-side-bar.item text="Office Locations" icon="map-pin" :route="route('office-locations.index')" wire:navigate />
                    @endcan
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
