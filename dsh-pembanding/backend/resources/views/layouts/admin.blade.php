<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Monitoring Perkembangan Pasien') - DSH Klinik</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 bg-slate-900 text-slate-200 flex flex-col shrink-0 shadow-lg">
            <!-- Clinic Brand -->
            <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-bold text-xl shadow-md">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div>
                    <h1 class="font-bold text-base tracking-wide text-white leading-tight">DSH Holistic</h1>
                    <p class="text-xs text-emerald-400 font-medium">Monitoring Pasien</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    <i class="fa-solid fa-chart-pie w-6"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.patients') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.patients*') || request()->routeIs('admin.examinations*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    <i class="fa-solid fa-user-injured w-6"></i>
                    <span>Data Pasien</span>
                </a>
                <a href="{{ route('admin.activities') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.activities*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    <i class="fa-solid fa-person-walking w-6"></i>
                    <span>Aktivitas Mandiri (MHLS)</span>
                </a>

                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Arsitektur & Engine
                </div>
                <a href="{{ route('admin.monitoring') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.monitoring') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    <i class="fa-solid fa-network-wired w-6"></i>
                    <span>Outbox & Temporal</span>
                </a>
                <a href="http://localhost:8088" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                    <div class="flex items-center">
                        <i class="fa-solid fa-cubes w-6 text-indigo-400"></i>
                        <span>Temporal UI</span>
                    </div>
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs opacity-60"></i>
                </a>
            </nav>

            <!-- User profile & Logout -->
            <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-200">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="text-xs">
                        <p class="font-semibold text-white truncate max-w-[110px]">{{ Auth::user()->name ?? 'Petugas DSH' }}</p>
                        <p class="text-slate-400 capitalize">{{ Auth::user()->role ?? 'Petugas' }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-rose-400 p-1.5 rounded transition" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Topbar banner -->
            <header class="bg-white border-b border-slate-200 px-6 py-3.5 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        EDA + Temporal Active
                    </span>
                    <span class="text-xs text-slate-500 hidden sm:inline">Klinik Darus Syifa Holistik</span>
                </div>
                <div class="text-xs text-slate-600 font-medium">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            </header>

            <!-- Flash alerts -->
            <div class="px-6 pt-4">
                @if(session('success'))
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-md shadow-sm mb-4 flex items-center justify-between">
                        <div class="flex items-center text-emerald-800 text-sm">
                            <i class="fa-solid fa-circle-check text-emerald-600 mr-3 text-lg"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-md shadow-sm mb-4">
                        <div class="flex items-start text-rose-800 text-sm">
                            <i class="fa-solid fa-circle-exclamation text-rose-600 mr-3 text-lg mt-0.5"></i>
                            <div>
                                <p class="font-semibold">Terjadi kesalahan pada input:</p>
                                <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs text-rose-700">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <div class="flex-1 p-6">
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="bg-white border-t border-slate-200 px-6 py-3 text-xs text-slate-500 text-center">
                Sistem Monitoring Perkembangan Pasien &copy; {{ date('Y') }} — Event-Driven Architecture with Temporal Durable Workflow
            </footer>
        </main>
    </div>
    @stack('scripts')
</body>
</html>

