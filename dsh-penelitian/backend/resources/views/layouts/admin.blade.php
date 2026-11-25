<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Monitoring Klinis') — Klinik Darus Syifa Holistik</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace !important;
        }
        [x-cloak] { display: none !important; }

        /* Color Tokens & Clean Styling */
        :root {
            --dsh-primary: #20596c;
            --dsh-primary-hover: #184452;
            --dsh-primary-active: #133642;
            --dsh-primary-50: #f0f7f9;
            --dsh-primary-100: #dceef2;
            --dsh-primary-200: #bedde5;
        }

        /* Sidebar Styling: Clean Clinical White with #20596c Highlights */
        .clinic-sidebar {
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
        }
        .clinic-group-label {
            color: #64748b;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0 16px;
            margin-top: 18px;
            margin-bottom: 6px;
        }
        .clinic-nav-link {
            color: #334155;
            font-size: 14.5px;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.15s ease-in-out;
            margin: 2px 10px;
        }
        .clinic-nav-link:hover {
            background-color: #f0f7f9;
            color: #20596c;
        }
        .clinic-nav-link.active {
            background-color: #20596c !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(32, 89, 108, 0.25);
        }
        .clinic-nav-link.active i {
            color: #ffffff !important;
        }

        /* Sub-navigation under Group */
        .clinic-sub-link {
            color: #475569;
            font-size: 13.5px;
            font-weight: 500;
            padding: 8px 16px 8px 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            transition: all 0.15s ease;
            margin: 2px 10px;
        }
        .clinic-sub-link:hover {
            background-color: #f0f7f9;
            color: #20596c;
        }
        .clinic-sub-link.active {
            background-color: #e6f1f4;
            color: #20596c;
            font-weight: 700;
        }

        /* Buttons */
        .btn-dsh {
            background-color: #20596c;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            border-radius: 12px;
            padding: 10px 18px;
            transition: all 0.15s ease-in-out;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .btn-dsh:hover {
            background-color: #184452;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(32, 89, 108, 0.2);
        }

        /* Cards */
        .clinic-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        /* Clean Scrollbars */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        ::-webkit-scrollbar-track { background: transparent; }
    </style>
</head>
<body class="h-full antialiased bg-[#f8fafc] text-slate-800" x-data="{ sidebarOpen: false, engineOpen: false }">

<div class="min-h-screen flex flex-col lg:flex-row">

    <!-- Mobile Backdrop Overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
    </div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="clinic-sidebar fixed lg:static inset-y-0 left-0 z-50 w-72 flex flex-col shrink-0 shadow-lg lg:shadow-none transition-transform duration-200 ease-in-out"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <!-- Logo & Brand Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-200 bg-white">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white shadow-sm" style="background-color: #20596c;">
                    <i class="fa-solid fa-hospital-user text-xl"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 text-base tracking-tight block leading-tight">DARUS SYIFA</span>
                    <span class="text-xs font-semibold tracking-wider uppercase block" style="color:#20596c;">Klinik Holistik</span>
                </div>
            </a>
            <button type="button" @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600 p-2">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Navigation Menus -->
        <nav class="flex-1 py-4 overflow-y-auto">

            <!-- DASHBOARD -->
            <div class="mb-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="clinic-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-base {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-[#20596c]' }}"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- GROUP: DATA PASIEN -->
            <div>
                <div class="clinic-group-label">Data Pasien</div>
                <a href="{{ route('admin.patients') }}"
                   class="clinic-nav-link {{ request()->routeIs('admin.patients') && !request()->routeIs('admin.patients.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-users w-5 text-center text-base {{ request()->routeIs('admin.patients') && !request()->routeIs('admin.patients.create') ? 'text-white' : 'text-[#20596c]' }}"></i>
                    <span>Daftar Pasien</span>
                </a>
                <a href="{{ route('admin.patients.create') }}"
                   class="clinic-sub-link {{ request()->routeIs('admin.patients.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-plus mr-2 text-xs"></i>
                    <span>Tambah Pasien Baru</span>
                </a>
            </div>

            <!-- GROUP: JADWAL & KUNJUNGAN -->
            <div>
                <div class="clinic-group-label">Jadwal &amp; Kunjungan</div>
                <a href="{{ route('admin.visits') }}"
                   class="clinic-nav-link {{ request()->routeIs('admin.visits') && !request()->routeIs('admin.visits.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check w-5 text-center text-base {{ request()->routeIs('admin.visits') && !request()->routeIs('admin.visits.create') ? 'text-white' : 'text-[#20596c]' }}"></i>
                    <span>Jadwal Kunjungan</span>
                </a>
                <a href="{{ route('admin.visits.create') }}"
                   class="clinic-sub-link {{ request()->routeIs('admin.visits.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-plus mr-2 text-xs"></i>
                    <span>Buat Jadwal Baru</span>
                </a>
            </div>

            <!-- GROUP: PEMERIKSAAN KLINIS -->
            <div>
                <div class="clinic-group-label">Pemeriksaan Klinis</div>
                <a href="{{ route('admin.examinations.index') }}"
                   class="clinic-nav-link {{ request()->routeIs('admin.examinations.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-stethoscope w-5 text-center text-base {{ request()->routeIs('admin.examinations.index') ? 'text-white' : 'text-[#20596c]' }}"></i>
                    <span>Pemeriksaan Pasien</span>
                </a>
            </div>

            <!-- GROUP: MONITORING PERKEMBANGAN -->
            <div>
                <div class="clinic-group-label">Monitoring Perkembangan</div>
                <a href="{{ route('admin.monitoring.progress') }}"
                   class="clinic-nav-link {{ request()->routeIs('admin.monitoring.progress') || request()->routeIs('admin.examinations.charts') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line w-5 text-center text-base {{ request()->routeIs('admin.monitoring.progress') || request()->routeIs('admin.examinations.charts') ? 'text-white' : 'text-[#20596c]' }}"></i>
                    <span>Grafik Perkembangan</span>
                </a>
            </div>

            <!-- GROUP: AKTIVITAS MANDIRI MHLS -->
            <div>
                <div class="clinic-group-label">Aktivitas Mandiri MHLS</div>
                <a href="{{ route('admin.activities') }}"
                   class="clinic-nav-link {{ request()->routeIs('admin.activities') && !request()->routeIs('admin.activities.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-person-walking w-5 text-center text-base {{ request()->routeIs('admin.activities') && !request()->routeIs('admin.activities.create') ? 'text-white' : 'text-[#20596c]' }}"></i>
                    <span>Instruksi MHLS</span>
                </a>
                <a href="{{ route('admin.activities.create') }}"
                   class="clinic-sub-link {{ request()->routeIs('admin.activities.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-plus mr-2 text-xs"></i>
                    <span>Buat Instruksi Baru</span>
                </a>
            </div>

            <!-- GROUP: ENGINE & ARSITEKTUR (COLLAPSIBLE / ACCORDION) -->
            <div class="pt-4 mt-4 border-t border-slate-200">
                <button type="button" @click="engineOpen = !engineOpen"
                        class="w-full flex items-center justify-between py-2 px-5 text-slate-500 hover:text-slate-800 text-xs font-bold uppercase tracking-wider select-none transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-server text-sm text-slate-400"></i>
                        Engine &amp; Arsitektur
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                       :class="engineOpen ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="engineOpen" x-cloak class="mt-1 space-y-0.5">
                    <a href="{{ route('admin.engine.events') }}"
                       class="clinic-sub-link {{ request()->routeIs('admin.engine.events') || request()->routeIs('admin.monitoring') ? 'active' : '' }}">
                        <i class="fa-solid fa-network-wired mr-2 text-xs"></i>
                        <span>Outbox Event Log</span>
                    </a>
                    <a href="http://localhost:8088" target="_blank"
                       class="clinic-sub-link">
                        <i class="fa-solid fa-cubes mr-2 text-xs text-indigo-500"></i>
                        <span class="flex-1">Temporal Web UI</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                    </a>
                </div>
            </div>

        </nav>

        <!-- Profile Bar at Sidebar Bottom -->
        <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex items-center justify-between">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-sm shrink-0" style="background-color: #20596c;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="truncate">
                    <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->name ?? 'Petugas Klinik' }}</p>
                    <p class="text-xs text-slate-500 truncate capitalize">{{ Auth::user()->role ?? 'Admin' }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Keluar Sistem"
                        class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

        <!-- Topbar Header -->
        <header class="h-20 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Button -->
                <button type="button" @click="sidebarOpen = true"
                        class="lg:hidden p-2.5 rounded-xl text-slate-600 hover:bg-slate-100 transition">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <!-- Status Badge -->
                <div class="hidden sm:flex items-center gap-2.5">
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold" style="background-color: #f0f7f9; color: #20596c; border: 1px solid #bedde5;">
                        <span class="w-2.5 h-2.5 rounded-full mr-2" style="background-color: #20596c;"></span>
                        Sistem Pelayanan Aktif
                    </span>
                    <span class="text-sm text-slate-500 font-medium">| Klinik Darus Syifa Holistik</span>
                </div>
            </div>

            <!-- Topbar Right Items -->
            <div class="flex items-center gap-4">
                <div class="text-sm text-slate-600 font-semibold hidden md:flex items-center">
                    <i class="fa-regular fa-calendar mr-2 text-base" style="color: #20596c;"></i>
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
                <div class="h-5 w-px bg-slate-200 hidden md:block"></div>
                <div class="text-sm font-bold text-slate-800">
                    {{ Auth::user()->name }}
                </div>
            </div>
        </header>

        <!-- Flash Alert Messages -->
        <div class="px-6 sm:px-8 pt-6">
            @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
            @endif

            @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-xs">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
            @endif
        </div>

        <!-- Main Body -->
        <main class="flex-1 p-6 sm:p-8">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white px-6 sm:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500">
            <div>
                &copy; {{ date('Y') }} <strong class="text-slate-700">Klinik Darus Syifa Holistik</strong>. Seluruh Hak Cipta Dilindungi.
            </div>
            <div class="text-xs font-medium text-slate-400">
                Sistem Monitoring Perkembangan Pasien &bull; Standar Pelayanan Medis
            </div>
        </footer>

    </div>

</div>

@stack('scripts')
</body>
</html>
