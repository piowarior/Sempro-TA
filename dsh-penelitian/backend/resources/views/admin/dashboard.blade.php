@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gradient-to-r from-slate-900 to-slate-800 p-6 rounded-2xl text-white shadow-md">
        <div>
            <h2 class="text-xl font-bold">Selamat Datang, {{ Auth::user()->name }}</h2>
            <p class="text-slate-400 text-xs mt-1">Dashboard Monitoring Perkembangan Pasien DSH & Orchestrator Workflow</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.patients.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-500 rounded-xl text-xs font-semibold shadow transition">
                <i class="fa-solid fa-user-plus mr-2"></i> Pasien Baru
            </a>
            <a href="{{ route('admin.activities.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-xs font-semibold shadow transition">
                <i class="fa-solid fa-plus-circle mr-2"></i> Instruksi MHLS
            </a>
        </div>
    </div>

    <!-- Stat KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Pasien -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Pasien</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $totalPatients }}</h3>
                <span class="text-xs text-slate-500">Pasien Terdaftar</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Card 2: Sesi Pemeriksaan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Sesi Pemeriksaan</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $totalSessions }}</h3>
                <span class="text-xs text-slate-500">Hasil Evaluasi Fisik & Toksin</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-notes-medical"></i>
            </div>
        </div>

        <!-- Card 3: Outbox Events -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Outbox Events</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl font-bold text-slate-800">{{ $totalOutbox }}</h3>
                    @if($pendingOutbox > 0)
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">{{ $pendingOutbox }} pending</span>
                    @else
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Semua sinkron</span>
                    @endif
                </div>
                <span class="text-xs text-slate-500">Transactional Outbox</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-box-archive"></i>
            </div>
        </div>

        <!-- Card 4: Workflows Temporal -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Temporal Workflows</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl font-bold text-slate-800">{{ $activeWorkflows }}</h3>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">Running</span>
                </div>
                <span class="text-xs text-slate-500">Durable Timer & Signal</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-diagram-project"></i>
            </div>
        </div>
    </div>

    <!-- 2 Column Layout: Recent Patients & Active Plans -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Col 1: Pasien Terdaftar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-user-injured text-emerald-600"></i>
                    <h3 class="font-bold text-slate-800 text-sm">Pasien Terbaru</h3>
                </div>
                <a href="{{ route('admin.patients') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentPatients as $p)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-slate-800">{{ $p->name }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $p->patient_type === 'mhls' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ strtoupper($p->patient_type) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $p->patient_number }} &bull; {{ $p->sessions->count() }} Sesi Pemeriksaan</p>
                        </div>
                        <a href="{{ route('admin.patients.detail', $p->id) }}" class="text-xs px-3 py-1.5 bg-slate-100 hover:bg-slate-200 font-medium rounded-lg text-slate-700 transition">
                            Detail
                        </a>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">Belum ada pasien terdaftar.</p>
                @endforelse
            </div>
        </div>

        <!-- Col 2: Instruksi Aktivitas Mandiri (MHLS) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-person-walking text-indigo-600"></i>
                    <h3 class="font-bold text-slate-800 text-sm">Aktivitas Mandiri & Workflow</h3>
                </div>
                <a href="{{ route('admin.activities') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lihat Semua &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentPlans as $plan)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-slate-800">{{ $plan->name }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $plan->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($plan->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Pasien: <strong class="text-slate-700">{{ $plan->patient->name }}</strong> &bull; Jam: {{ substr($plan->reminder_time, 0, 5) }}
                            </p>
                        </div>
                        <a href="{{ route('admin.activities.detail', $plan->id) }}" class="text-xs px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 font-medium rounded-lg text-indigo-700 transition">
                            Pantau
                        </a>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">Belum ada aktivitas mandiri dibuat.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- EDA & Temporal Live Event Stream Banner -->
    <div class="bg-slate-900 rounded-2xl p-5 text-white shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <h3 class="font-bold text-sm text-slate-100">Live Transactional Outbox Events</h3>
            </div>
            <a href="{{ route('admin.monitoring') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">
                Buka Monitor Arsitektur &rarr;
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400">
                        <th class="py-2">Event ID</th>
                        <th class="py-2">Event Type</th>
                        <th class="py-2">Aggregate</th>
                        <th class="py-2">Occurred At</th>
                        <th class="py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($recentEvents as $e)
                        <tr>
                            <td class="py-2 text-slate-400 truncate max-w-[120px]">{{ $e->event_id }}</td>
                            <td class="py-2 text-emerald-300 font-bold">{{ $e->event_type }}</td>
                            <td class="py-2 text-slate-300">{{ $e->aggregate_type }} #{{ $e->aggregate_id }}</td>
                            <td class="py-2 text-slate-400">{{ $e->occurred_at->format('H:i:s d-m-Y') }}</td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-sans font-bold {{ $e->status === 'published' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-amber-950 text-amber-400 border border-amber-800' }}">
                                    {{ strtoupper($e->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-3 text-center text-slate-500 font-sans">Belum ada domain event.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

