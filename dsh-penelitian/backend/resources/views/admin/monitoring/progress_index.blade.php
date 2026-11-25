@extends('layouts.admin')

@section('title', 'Monitoring Perkembangan Pasien & 6 Grafik Analisis')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Monitoring Perkembangan Pasien</h1>
            <p class="text-sm text-slate-500 mt-1">
                Pilih pasien untuk melihat 6 visualisasi grafik klinis: Beban Toksin, Komposisi Tubuh, Glukosa &amp; GKI, Profil Darah, Tekanan Darah, dan Kepatuhan MHLS.
            </p>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.monitoring.progress') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i class="fa-solid fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#20596c]"
                       placeholder="Cari nama pasien atau nomor rekam medis...">
            </div>

            <div class="flex items-center gap-2">
                <select name="type" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#20596c]">
                    <option value="">Semua Tipe Pasien</option>
                    <option value="mhls" {{ request('type') === 'mhls' ? 'selected' : '' }}>Program MHLS</option>
                    <option value="umum" {{ request('type') === 'umum' ? 'selected' : '' }}>Pasien Umum</option>
                </select>
                <button type="submit" class="px-5 py-2.5 text-white text-sm font-semibold rounded-xl transition" style="background:#20596c;" onmouseover="this.style.background='#184452'" onmouseout="this.style.background='#20596c'">
                    Filter
                </button>
                @if(request('search') || request('type'))
                    <a href="{{ route('admin.monitoring.progress') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Patients Progress Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($patients as $pt)
            @php
                $latestSession = $pt->sessions->first();
                $sessionCount = $pt->sessions->count();
                $hasActivePlan = $pt->activityPlans->where('status', 'active')->count() > 0;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md hover:border-[#91c4d2] transition p-5 flex flex-col justify-between">
                <div>
                    <!-- Card Top -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl text-white flex items-center justify-center text-lg font-bold shadow-xs" style="background:linear-gradient(135deg, #20596c, #2d7d96);">
                                {{ strtoupper(substr($pt->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base leading-tight">{{ $pt->name }}</h3>
                                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $pt->patient_number }}</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full {{ $pt->patient_type === 'mhls' ? 'bg-[#f0f7f9] text-[#20596c] border border-[#bedde5]' : 'bg-slate-100 text-slate-600' }}">
                            {{ $pt->patient_type }}
                        </span>
                    </div>

                    <!-- Clinical Status Snapshot -->
                    <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-3 gap-2 text-center">
                        <div class="p-2 rounded-xl bg-slate-50">
                            <div class="text-[10px] text-slate-500 uppercase font-semibold">Total Sesi</div>
                            <div class="text-base font-bold text-slate-900 mt-0.5">{{ $sessionCount }}</div>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50">
                            <div class="text-[10px] text-slate-500 uppercase font-semibold">Toksin</div>
                            <div class="text-xs font-bold text-slate-800 mt-1">
                                {{ $latestSession?->toxinAssessment?->total_indicated ? $latestSession->toxinAssessment->total_indicated . '/50' : '-' }}
                            </div>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50">
                            <div class="text-[10px] text-slate-500 uppercase font-semibold">GKI Ratio</div>
                            <div class="text-xs font-bold text-[#20596c] mt-1">
                                {{ $latestSession?->bloodMeasurement?->gki ? number_format($latestSession->bloodMeasurement->gki, 2) : '-' }}
                            </div>
                        </div>
                    </div>

                    <!-- Latest Assessment Date -->
                    <div class="mt-3 text-xs text-slate-500 flex items-center justify-between">
                        <span>Pemeriksaan Terakhir:</span>
                        <strong class="text-slate-700">
                            {{ $latestSession ? $latestSession->session_date->format('d M Y') : 'Belum Ada' }}
                        </strong>
                    </div>

                    @if($pt->patient_type === 'mhls')
                        <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                            <span>Aktivitas MHLS:</span>
                            <span class="inline-flex items-center text-[11px] font-semibold {{ $hasActivePlan ? 'text-emerald-700' : 'text-slate-400' }}">
                                <i class="fa-solid fa-circle text-[7px] mr-1 {{ $hasActivePlan ? 'text-emerald-500' : 'text-slate-300' }}"></i>
                                {{ $hasActivePlan ? 'Aktif (Temporal Timer)' : 'Tidak Ada Plan Aktif' }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Action Button -->
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('admin.examinations.charts', $pt->id) }}"
                       class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-white text-xs font-bold shadow-xs transition"
                       style="background-color: #20596c;"
                       onmouseover="this.style.backgroundColor='#184452'"
                       onmouseout="this.style.backgroundColor='#20596c'">
                        <i class="fa-solid fa-chart-line mr-2"></i> Buka 6 Grafik Analisis
                    </a>
                    <a href="{{ route('admin.examinations.create', $pt->id) }}"
                       class="px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition"
                       title="Catat Sesi Baru">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 bg-white rounded-2xl border border-slate-200 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-base">Tidak Ada Pasien Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter tipe pasien.</p>
            </div>
        @endforelse
    </div>

    @if($patients->hasPages())
    <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs">
        {{ $patients->links() }}
    </div>
    @endif
</div>
@endsection
