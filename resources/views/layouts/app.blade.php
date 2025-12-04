{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '1ntegrated Metrology Corporation') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Per-page styles (e.g. DataTables CSS) --}}
    @stack('styles')

    <style>
        /* Hide scrollbars on targeted elements (sidebar) while keeping scroll functionality */
        .scroll-hide {
            scrollbar-width: none;
            -ms-overflow-style: none; /* IE/Edge */
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        .scroll-hide::-webkit-scrollbar {
            display: none;
        }

        /* Sidebar toggle helpers */
        .sidebar-open {
            transform: translateX(0);
        }
        .sidebar-closed {
            transform: translateX(-100%);
        }
        .main-open {
            margin-left: 16rem; /* 64 tailwind units */
        }
        .main-closed {
            margin-left: 0;
        }
    </style>
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100 relative">

    {{-- LEFT SIDEBAR --}}
    <aside id="sidebar"
        class="w-64 bg-white border-r border-slate-200 text-slate-700 flex flex-col fixed inset-y-0 left-0 z-40 transition-transform duration-200 scroll-hide sidebar-open"
        style="max-height: 100vh; overflow-y: auto;">

        {{-- Company Header --}}
        <div class="h-20 flex items-center px-4 border-b border-slate-200">
            <img src="{{ asset('images/imc-logo.png') }}"
                 class="h-12 w-auto mr-3"
                 alt="Logo">

            <div class="leading-tight">
                {{-- Line 1: 1ntegrated Metrology --}}
                <div class="flex items-baseline">
                    <span class="text-xl font-bold italic mr-1"
                          style="color:#0b5cb8; line-height:1;">
                        1
                    </span>
                    <span class="text-sm font-semibold text-slate-700">
                        ntegrated Metrology
                    </span>
                </div>

                {{-- Line 2: Corporation --}}
                <div class="text-sm font-semibold text-slate-700 ml-6 -mt-0.5">
                    Corporation
                </div>
            </div>
        </div>

        {{-- User --}}
        <div class="px-4 py-4 border-b border-slate-200">
            <div class="flex items-center space-x-3">

                {{-- Avatar (initial letter) --}}
                <div class="h-11 w-11 rounded-full bg-slate-100 border border-slate-300 flex items-center justify-center text-sm font-semibold text-slate-600">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div>
                    {{-- Display Name --}}
                    <div class="text-sm font-semibold text-slate-700">
                        {{ auth()->user()->display_name ?? auth()->user()->name }}
                    </div>

                    {{-- Dynamic Position --}}
                    <div class="text-xs text-slate-500">
                        {{ auth()->user()->position ?? 'No Position' }}
                    </div>
                </div>

            </div>
        </div>

        {{-- NAVIGATION --}}
        <nav x-data="{
                marketing: false,
                services: false,
                accounting: false,
                tools: false
            }"
             class="flex-1 mt-3 text-sm text-slate-700">

            {{-- MAIN Label --}}
            <div class="px-4 pb-2 text-[10px] tracking-[0.2em] uppercase text-slate-400">
                MAIN
            </div>

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center px-4 py-2.5 mx-2 rounded-lg mb-1
                      {{ request()->routeIs('dashboard')
                         ? 'bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200 shadow-sm'
                         : 'hover:bg-slate-100/70' }}">
                <div class="w-5 h-5 rounded-full bg-emerald-500/10 flex items-center justify-center mr-3">
                    ●
                </div>
                Dashboard
            </a>

            {{-- Customers --}}
            <a href="{{ route('customers.index') }}"
               class="flex items-center px-4 py-2.5 mx-2 rounded-lg mb-1
                      {{ request()->routeIs('customers.*') ? 'bg-slate-100/80 font-semibold text-emerald-700' : 'hover:bg-slate-100/70' }}">
                <div class="w-5 h-5 rounded-full bg-blue-400/10 flex items-center justify-center mr-3">
                    👥
                </div>
                Customers
            </a>

            {{-- OPERATIONS Label --}}
            <div class="px-4 pt-4 pb-2 text-[10px] tracking-[0.2em] uppercase text-slate-400">
                OPERATIONS
            </div>

            {{-- Marketing --}}
            <button @click="marketing = !marketing"
                    class="w-full flex items-center justify-between px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="flex items-center">
                    <div class="w-5 h-5 rounded-full bg-fuchsia-400/10 flex items-center justify-center mr-3">
                        ✦
                    </div>
                    Marketing
                </div>
                <span :class="marketing ? 'rotate-90 text-emerald-500' : 'text-slate-500'"
                      class="transition-transform">›</span>
            </button>
            <div x-show="marketing" x-transition class="ml-10 mt-1 mb-2 flex flex-col space-y-1 text-slate-600">
                <a href="{{ route('quotations.index') }}"
                   class="py-1 {{ request()->routeIs('quotations.*') ? 'text-emerald-600 font-semibold' : 'hover:text-emerald-600' }}">
                    Quotations
                </a>
                <a href="#" class="py-1 hover:text-emerald-600">Pipeline</a>
                <a href="#" class="py-1 hover:text-emerald-600">Customer Retention Report</a>
            </div>

            {{-- Service --}}
            <button @click="services = !services"
                    class="w-full flex items-center justify-between px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="flex items-center">
                    <div class="w-5 h-5 rounded-full bg-cyan-400/10 flex items-center justify-center mr-3">
                        ⚙
                    </div>
                    Service
                </div>
                <span :class="services ? 'rotate-90 text-emerald-500' : 'text-slate-500'"
                      class="transition-transform">›</span>
            </button>
            <div x-show="services" x-transition class="ml-10 mt-1 mb-2 flex flex-col space-y-1 text-slate-600">
                <a href="{{ route('receiving.index') }}" class="py-1 hover:text-emerald-600">Receiving</a>
                <a href="{{ route('technical.index') }}" class="py-1 hover:text-emerald-600">Technical</a>
                <a href="#" class="py-1 hover:text-emerald-600">Releasing</a>
                <a href="#" class="py-1 hover:text-emerald-600">Billing</a>
                <a href="#" class="py-1 hover:text-emerald-600">Accounts Receivable</a>
            </div>

            {{-- Accounting --}}
            <button @click="accounting = !accounting"
                    class="w-full flex items-center justify-between px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="flex items-center">
                    <div class="w-5 h-5 rounded-full bg-amber-400/10 flex items-center justify-center mr-3">
                        ₱
                    </div>
                    Accounting
                </div>
                <span :class="accounting ? 'rotate-90 text-emerald-500' : 'text-slate-500'"
                      class="transition-transform">›</span>
            </button>
            <div x-show="accounting" x-transition class="ml-10 mt-1 mb-2 flex flex-col space-y-1 text-slate-600">
                <a href="#" class="py-1 hover:text-emerald-600">Credit Memo</a>
                <a href="#" class="py-1 hover:text-emerald-600">Ledger</a>
                <a href="#" class="py-1 hover:text-emerald-600">Statement of Account</a>
                <a href="#" class="py-1 hover:text-emerald-600">Sales Summary</a>
                <a href="#" class="py-1 hover:text-emerald-600">OR Summary</a>
                <a href="#" class="py-1 hover:text-emerald-600">Aging</a>
            </div>

            {{-- User Accounts --}}
            <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="w-5 h-5 rounded-full bg-slate-400/10 flex items-center justify-center mr-3">
                    👤
                </div>
                User Accounts
            </a>

            {{-- User Groups --}}
            <a href="#" class="flex items-center px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="w-5 h-5 rounded-full bg-slate-400/10 flex items-center justify-center mr-3">
                    👥
                </div>
                User Groups
            </a>

            {{-- File Manager --}}
            <a href="#" class="flex items-center px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="w-5 h-5 rounded-full bg-purple-400/10 flex items-center justify-center mr-3">
                    📁
                </div>
                File Manager
            </a>

            {{-- Tools --}}
            <button @click="tools = !tools"
                    class="w-full flex items-center justify-between px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="flex items-center">
                    <div class="w-5 h-5 rounded-full bg-indigo-400/10 flex items-center justify-center mr-3">🧪</div>
                    Tools
                </div>
                <span :class="tools ? 'rotate-90 text-emerald-500' : 'text-slate-500'"
                      class="transition-transform">›</span>
            </button>
            <div x-show="tools" x-transition class="ml-10 mt-1 mb-2 flex flex-col space-y-1 text-slate-600">
                <a href="#" class="py-1 hover:text-emerald-600">Standard Instruments</a>
                <a href="#" class="py-1 hover:text-emerald-600">Datasheets</a>
                <a href="#" class="py-1 hover:text-emerald-600">Forms</a>
                <a href="#" class="py-1 hover:text-emerald-600">Pricing</a>
                <a href="#" class="py-1 hover:text-emerald-600">Suppliers</a>
                <a href="#" class="py-1 hover:text-emerald-600">Calibration Methods</a>
            </div>

            {{-- Reports --}}
            <a href="#" class="flex items-center px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="w-5 h-5 rounded-full bg-emerald-400/10 flex items-center justify-center mr-3">
                    📊
                </div>
                Reports
            </a>

            {{-- Roles --}}
            <a href="#" class="flex items-center px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="w-5 h-5 rounded-full bg-red-400/10 flex items-center justify-center mr-3">
                    🔑
                </div>
                Roles & Permissions
            </a>

            {{-- Customer Portal --}}
            <a href="#" class="flex items-center px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="w-5 h-5 rounded-full bg-cyan-400/10 flex items-center justify-center mr-3">
                    🌐
                </div>
                Customer Portal
            </a>

            {{-- Audit Trail --}}
            <a href="#" class="flex items-center px-4 py-2.5 mx-2 rounded-lg mb-1 hover:bg-slate-100/70">
                <div class="w-5 h-5 rounded-full bg-orange-400/10 flex items-center justify-center mr-3">
                    🕒
                </div>
                Audit Trail
            </a>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="mt-4 mx-2 mb-3">
                @csrf
                <button type="submit"
                        class="w-full flex items-center px-4 py-2.5 rounded-lg hover:bg-red-50 text-red-600 font-semibold">
                    <div class="w-5 h-5 rounded-full bg-red-500/10 flex items-center justify-center mr-3">
                        ⏻
                    </div>
                    Logout
                </button>
            </form>
        </nav>
    </aside>

    {{-- RIGHT SIDE: TOP BAR + PAGE CONTENT --}}
    <div id="main-content" class="flex flex-col min-h-screen transition-all duration-200 overflow-y-auto main-open">
        {{-- Top bar --}}
        <header class="bg-white shadow sticky top-0 z-30">
            <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between space-x-4">
                    {{-- Toggle sidebar --}}
                    <button type="button" id="navToggle"
                            class="inline-flex items-center justify-center h-10 w-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100"
                            aria-label="Toggle navigation">
                        ☰
                    </button>

                    {{-- Center: search (global dummy) --}}
                    <div class="hidden md:block flex-1 max-w-xl mx-6">
                        <div class="relative">
                            <input
                                type="text"
                                placeholder="Search for something..."
                                class="w-full rounded-full border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring focus:ring-emerald-400"
                            >
                        </div>
                    </div>

                    {{-- Right: Calibration button + logout --}}
                    <div class="flex items-center space-x-4">
                        <a href="#"
                           class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-full">
                            Calibration
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="text-xs sm:text-sm text-gray-600 hover:text-red-600 font-semibold">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="pb-10">
            @isset($header)
                <div class="bg-white shadow mb-4">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </div>
            @endisset
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>
        </main>
    </div>
</div>

{{-- Per-page scripts (e.g. DataTables JS) --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('main-content');
        const toggle = document.getElementById('navToggle');

        if (!sidebar || !main || !toggle) {
            return;
        }

        const closeSidebar = () => {
            sidebar.classList.remove('sidebar-open');
            sidebar.classList.add('sidebar-closed');
            main.classList.remove('main-open');
            main.classList.add('main-closed');
        };

        const openSidebar = () => {
            sidebar.classList.add('sidebar-open');
            sidebar.classList.remove('sidebar-closed');
            main.classList.remove('main-closed');
            main.classList.add('main-open');
        };

        toggle.addEventListener('click', () => {
            const isOpen = sidebar.classList.contains('sidebar-open') && !sidebar.classList.contains('sidebar-closed');
            if (isOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>
