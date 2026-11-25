@extends('layouts.admin')

@section('title', 'Dashboard Pelayanan Klinis')

@section('content')
<div class="space-y-8">
    <!-- Welcome Banner Klinis Bersih & Modern -->
    <div class="rounded-3xl p-7 sm:p-8 text-white shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-6"
         style="background: linear-gradient(135deg, #20596c 0%, #174250 100%);">
        <div>
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold mb-3" style="background: rgba(255, 255, 255, 0.16); color: #ffffff;">
                <i class="fa-solid fa-hospital text-sm"></i> Panel Petugas &amp; Dokter
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Selamat Datang, {{ Auth::user()->name }}</h1>
            <p class="text-sm sm:text-base mt-2 text-slate-100 max-w-2xl leading-relaxed">
                Pusat kendali pemantauan klinis, pencatatan rekam medis 5 kelompok uji, evaluasi program MHLS, dan jadwal kunjungan pasien.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.patients.create') }}" class="inline-flex items-center px-5 py-3 rounded-2xl text-sm font-bold shadow-sm transition bg-white text-[#20596c] hover:bg-slate-50">
                <i class="fa-solid fa-user-plus mr-2.5"></i> Pasien Baru
            </a>
            <a href="{{ route('admin.visits.create') }}" class="inline-flex items-center px-5 py-3 rounded-2xl text-sm font-bold shadow-sm transition text-white border border-white/30 hover:bg-white/10" style="background: rgba(255, 255, 255, 0.1);">
                <i class="fa-solid fa-calendar-plus mr-2.5"></i> Buat Jadwal
            </a>
        </div>
    </div>

    <!-- 4 KPI Stat Cards Klinis (Bersih & Font Gede) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Pasien -->
        <div class="clinic-card p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pasien Terdaftar</span>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl text-[#20596c]" style="background-color: #f0f7f9;">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900">{{ $totalPatients }}</div>
                <div class="flex items-center gap-2 mt-2 text-xs font-semibold text-slate-500">
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-bold">{{ $totalMhlsPatients }} MHLS</span>
                    <span>&bull;</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold">{{ $totalUmumPatients }} Umum</span>
                </div>
            </div>
        </div>

        <!-- Sesi Pemeriksaan -->
        <div class="clinic-card p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sesi Pemeriksaan</span>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl text-[#20596c]" style="background-color: #f0f7f9;">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900">{{ $totalSessions }}</div>
                <p class="text-xs font-semibold text-slate-500 mt-2">
                    Evaluasi 5 Kelompok Parameter Klinis
                </p>
            </div>
        </div>

        <!-- Jadwal Kunjungan -->
        <div class="clinic-card p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Jadwal Kunjungan</span>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl text-blue-600 bg-blue-50">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900">{{ $totalVisits }}</div>
                <div class="flex items-center gap-2 mt-2 text-xs font-semibold">
                    @if($pendingVisits > 0)
                        <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-bold">{{ $pendingVisits }} Butuh Konfirmasi</span>
                    @else
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold">{{ $confirmedVisits }} Terkonfirmasi</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Instruksi Gaya Hidup MHLS -->
        <div class="clinic-card p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Aktivitas Mandiri MHLS</span>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl text-purple-600 bg-purple-50">
                    <i class="fa-solid fa-person-walking"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900">{{ $activePlans }}</div>
                <p class="text-xs font-semibold text-slate-500 mt-2">
                    Instruksi Pola Hidup &amp; Puasa Aktif
                </p>
            </div>
        </div>

    </div>

    <!-- Grafik Tren Pelayanan Klinis (Clean Chart) -->
    <div class="clinic-card p-6 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-5 mb-5 border-b border-slate-100 gap-3">
            <div>
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2.5">
                    <i class="fa-solid fa-chart-line text-[#20596c]"></i>
                    Tren Kunjungan &amp; Pemeriksaan Klinis Harian
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Frekuensi rekam pemeriksaan kesehatan pasien di Klinik Darus Syifa</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.examinations.index') }}" class="btn-dsh text-xs py-2 px-3.5">
                    Lihat Seluruh Pemeriksaan &rarr;
                </a>
            </div>
        </div>
        <div class="h-64 sm:h-72 relative">
            <canvas id="clinicalTrendChart"></canvas>
        </div>
    </div>

    <!-- 2 Kolom Grid Informasi Klinis (Pasien Terbaru & Jadwal Mendatang) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Kolom 1: Pasien Terbaru Terdaftar -->
        <div class="clinic-card p-6 sm:p-7">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold" style="background-color: #20596c;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Pasien Terbaru</h3>
                </div>
                <a href="{{ route('admin.patients') }}" class="text-xs font-bold text-[#20596c] hover:underline">
                    Buka Data Pasien &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentPatients as $p)
                    <div class="py-3.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-extrabold text-sm text-white" style="background-color: #20596c;">
                                {{ strtoupper(substr($p->name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-slate-900">{{ $p->name }}</h4>
                                <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500">
                                    <span class="font-mono">{{ $p->patient_number }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $p->sessions->count() }} Sesi Rekam</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2.5 py-1 rounded-full font-bold uppercase {{ $p->patient_type === 'mhls' ? 'bg-[#f0f7f9] text-[#20596c] border border-[#bedde5]' : 'bg-slate-100 text-slate-700' }}">
                                {{ $p->patient_type }}
                            </span>
                            <a href="{{ route('admin.patients.detail', $p->id) }}" class="p-2 text-slate-400 hover:text-[#20596c] transition" title="Lihat Rekam Medis">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-sm">Belum ada pasien terdaftar.</div>
                @endforelse
            </div>
        </div>

        <!-- Kolom 2: Jadwal Kunjungan Mendatang -->
        <div class="clinic-card p-6 sm:p-7">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Jadwal Kunjungan Pasien</h3>
                </div>
                <a href="{{ route('admin.visits') }}" class="text-xs font-bold text-[#20596c] hover:underline">
                    Kelola Jadwal &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($upcomingVisits as $v)
                    <div class="py-3.5 flex items-center justify-between gap-4">
                        <div>
                            <div class="font-bold text-sm text-slate-900">{{ $v->patient->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                <span><i class="fa-regular fa-calendar mr-1"></i>{{ $v->visit_date->format('d M Y') }}</span>
                                <span>&bull;</span>
                                <span class="font-mono font-semibold">{{ $v->time_slot }}</span>
                            </div>
                        </div>
                        <div>
                            @if($v->status === 'pending_confirmation')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Konfirmasi
                                </span>
                            @elseif($v->status === 'confirmed')
                                <a href="{{ route('admin.examinations.create', $v->patient_id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold transition text-[#20596c]" style="background-color: #f0f7f9;">
                                    <i class="fa-solid fa-stethoscope mr-1"></i> Periksa
                                </a>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                    {{ ucfirst($v->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-sm">Tidak ada jadwal kunjungan mendatang.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Sesi Pemeriksaan Klinis Terakhir -->
    <div class="clinic-card p-6 sm:p-7 overflow-hidden">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-notes-medical text-[#20596c]"></i>
                    Catatan Pemeriksaan Pasien Terkini
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Hasil rekam medis 5 kelompok indikator kesehatan pasien</p>
            </div>
            <a href="{{ route('admin.examinations.index') }}" class="text-xs font-bold text-[#20596c] hover:underline">
                Lihat Semua Rekam Medis &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-bold text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Pasien</th>
                        <th class="py-3 px-4">Tanggal / Sesi</th>
                        <th class="py-3 px-4">Beban Toksin</th>
                        <th class="py-3 px-4">Berat Tubuh</th>
                        <th class="py-3 px-4">Tekanan Darah</th>
                        <th class="py-3 px-4">GKI Ratio</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentSessions as $s)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-900 block">{{ $s->patient->name }}</span>
                            <span class="text-xs font-mono text-slate-500">{{ $s->patient->patient_number }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-semibold block">{{ $s->session_date->format('d M Y') }}</span>
                            <span class="text-xs text-slate-500 font-mono">{{ $s->session_number }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-900">{{ $s->toxinAssessment?->total_indicated ?? '-' }}/50</span>
                        </td>
                        <td class="py-3 px-4 font-mono">
                            {{ $s->bodyMeasurement?->weight ? number_format($s->bodyMeasurement->weight, 1) . ' kg' : '-' }}
                        </td>
                        <td class="py-3 px-4 font-mono">
                            {{ $s->bloodMeasurement?->systolic ? $s->bloodMeasurement->systolic . '/' . $s->bloodMeasurement->diastolic : ($s->bloodMeasurement?->blood_pressure ?: '-') }} mmHg
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-[#20596c]">
                            {{ $s->bloodMeasurement?->gki ? number_format($s->bloodMeasurement->gki, 2) : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.examinations.charts', $s->patient_id) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-[#20596c] hover:bg-[#dceef2] transition" style="background-color: #f0f7f9;">
                                <i class="fa-solid fa-chart-line mr-1.5"></i> 6 Grafik
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 text-sm">Belum ada rekam sesi pemeriksaan klinis.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const trendCtx = document.getElementById('clinicalTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendLabels) !!},
                datasets: [{
                    label: 'Sesi Pemeriksaan',
                    data: {!! json_encode($trendCounts) !!},
                    borderColor: '#20596c',
                    backgroundColor: 'rgba(32, 89, 108, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#20596c',
                    pointBorderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#174250',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 12 } },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 12 } }
                    }
                }
            }
        });
    }
</script>
@endpush
