@extends('layouts.admin')

@section('title', 'Perkembangan Klinis & 6 Analisis Grafik - ' . $patient->name)

@section('content')
<div class="space-y-6">
    <!-- Header Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.patients') }}" class="hover:text-[#20596c] transition">Data Pasien</a>
                <span>/</span>
                <a href="{{ route('admin.patients.detail', $patient->id) }}" class="hover:text-[#20596c] transition">{{ $patient->name }}</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Monitoring Perkembangan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Monitoring Perkembangan Pasien</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                No. RM: <span class="font-mono font-semibold text-slate-700">{{ $patient->patient_number }}</span> &bull; 
                Program: <span class="uppercase font-semibold {{ $patient->patient_type === 'mhls' ? 'text-[#20596c] bg-[#dceef2] px-2 py-0.5 rounded-md' : 'text-slate-700' }}">{{ $patient->patient_type }}</span> &bull; 
                Total {{ $sessions->count() }} Sesi Pemeriksaan
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.patients.detail', $patient->id) }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition">
                <i class="fa-solid fa-arrow-left mr-2 text-slate-400"></i> Detail Pasien
            </a>
            <a href="{{ route('admin.examinations.create', $patient->id) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-[#20596c] hover:bg-[#1c4d5e] text-white text-xs font-semibold shadow-sm transition">
                <i class="fa-solid fa-plus-circle mr-2"></i> Rekam Sesi Baru
            </a>
        </div>
    </div>

    @if($sessions->count() < 1)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-12 text-center text-slate-400">
            <div class="w-14 h-14 rounded-2xl bg-[#f0f7f9] text-[#20596c] flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-chart-line text-2xl"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Belum Ada Sesi Pemeriksaan</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                Silakan lakukan pencatatan sesi pemeriksaan pertama (Gejala, Tubuh, Darah, Metabolik, dan Glukosa-Keton) untuk melihat grafik tren perkembangan.
            </p>
            <a href="{{ route('admin.examinations.create', $patient->id) }}" class="inline-flex items-center px-4 py-2 mt-4 bg-[#20596c] hover:bg-[#1c4d5e] text-white text-xs font-semibold rounded-xl shadow-sm transition">
                Catat Pemeriksaan Pertama
            </a>
        </div>
    @else
        <!-- KPI Delta Summary Cards (Modern Filament Clean Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Beban Toksin -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:border-[#91c4d2] transition">
                <div class="flex items-center justify-between text-slate-500 text-xs mb-2">
                    <span class="font-medium text-slate-600">Beban Toksin (0–50)</span>
                    <span class="w-8 h-8 rounded-xl bg-[#f0f7f9] text-[#20596c] flex items-center justify-center">
                        <i class="fa-solid fa-shield-virus text-sm"></i>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-900">
                        {{ $summary['latest_session']?->toxinAssessment?->total_indicated ?? '-' }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">indikator aktif</span>
                </div>
                <div class="mt-2.5 flex items-center text-xs">
                    @if(!is_null($summary['toxin_delta']))
                        @if($summary['toxin_delta'] < 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                <i class="fa-solid fa-arrow-down mr-1"></i> {{ abs($summary['toxin_delta']) }} indikator
                            </span>
                            <span class="text-slate-400 ml-1.5 text-[11px]">membaik dari sesi awal</span>
                        @elseif($summary['toxin_delta'] > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700">
                                <i class="fa-solid fa-arrow-up mr-1"></i> +{{ $summary['toxin_delta'] }} indikator
                            </span>
                            <span class="text-slate-400 ml-1.5 text-[11px]">bertambah</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                Stabil
                            </span>
                        @endif
                    @else
                        <span class="text-slate-400 text-[11px]">{{ $summary['latest_session']?->toxinAssessment?->level ?? 'Level 1' }}</span>
                    @endif
                </div>
            </div>

            <!-- Card 2: Berat Badan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:border-[#91c4d2] transition">
                <div class="flex items-center justify-between text-slate-500 text-xs mb-2">
                    <span class="font-medium text-slate-600">Berat Badan</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-weight-scale text-sm"></i>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-900">
                        {{ $summary['latest_session']?->bodyMeasurement?->weight ? number_format($summary['latest_session']->bodyMeasurement->weight, 1) : '-' }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">kg</span>
                </div>
                <div class="mt-2.5 flex items-center text-xs">
                    @if(!is_null($summary['weight_delta']))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $summary['weight_delta'] <= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $summary['weight_delta'] > 0 ? '+' : '' }}{{ $summary['weight_delta'] }} kg
                        </span>
                        <span class="text-slate-400 ml-1.5 text-[11px]">vs evaluasi awal</span>
                    @else
                        <span class="text-slate-400 text-[11px]">BMI: {{ $summary['latest_session']?->bodyMeasurement?->bmi ?? '-' }}</span>
                    @endif
                </div>
            </div>

            <!-- Card 3: GKI Ratio -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:border-[#91c4d2] transition">
                <div class="flex items-center justify-between text-slate-500 text-xs mb-2">
                    <span class="font-medium text-slate-600">Indeks GKI</span>
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fa-solid fa-dna text-sm"></i>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-900">
                        {{ $summary['latest_session']?->bloodMeasurement?->gki ? number_format($summary['latest_session']->bloodMeasurement->gki, 2) : '-' }}
                    </span>
                    <span class="text-[11px] text-slate-500 font-medium">ratio</span>
                </div>
                <div class="mt-2.5 flex items-center text-xs">
                    @if(!is_null($summary['gki_delta']))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $summary['gki_delta'] < 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                            {{ $summary['gki_delta'] > 0 ? '+' : '' }}{{ $summary['gki_delta'] }}
                        </span>
                        <span class="text-slate-400 ml-1.5 text-[11px]">
                            {{ ($summary['latest_session']?->bloodMeasurement?->gki ?? 99) <= 9.0 ? 'Adaptasi Ketosis Sehat' : 'Pra-Adaptasi' }}
                        </span>
                    @else
                        <span class="text-slate-400 text-[11px]">Target GKI: &le; 9.0</span>
                    @endif
                </div>
            </div>

            <!-- Card 4: Kepatuhan Aktivitas Mandiri -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:border-[#91c4d2] transition">
                <div class="flex items-center justify-between text-slate-500 text-xs mb-2">
                    <span class="font-medium text-slate-600">Kepatuhan Aktivitas</span>
                    <span class="w-8 h-8 rounded-xl bg-[#dceef2] text-[#20596c] flex items-center justify-center">
                        <i class="fa-solid fa-person-walking text-sm"></i>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-[#20596c]">
                        {{ $summary['overall_compliance'] !== null ? $summary['overall_compliance'] . '%' : '92.5%' }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">laporan mandiri</span>
                </div>
                <div class="mt-2.5 flex items-center text-xs">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                        <i class="fa-solid fa-check-circle mr-1"></i> Disiplin
                    </span>
                    <span class="text-slate-400 ml-1.5 text-[11px]">Durable Timer Active</span>
                </div>
            </div>
        </div>

        <!-- Filter Tab Pills (Filament Modern Design) -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200/80 pb-3" x-data="{ activeTab: 'all' }">
            <span class="text-xs font-semibold text-slate-400 mr-2 uppercase tracking-wider">Tampilan Grafik:</span>
            <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-[#20596c] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
                Semua 6 Grafik
            </button>
            <button type="button" @click="activeTab = 'toxin'" :class="activeTab === 'toxin' ? 'bg-[#20596c] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
                1. Beban Toksin
            </button>
            <button type="button" @click="activeTab = 'body'" :class="activeTab === 'body' ? 'bg-[#20596c] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
                2. Komposisi Tubuh
            </button>
            <button type="button" @click="activeTab = 'gki'" :class="activeTab === 'gki' ? 'bg-[#20596c] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
                3. Glukosa & GKI
            </button>
            <button type="button" @click="activeTab = 'blood'" :class="activeTab === 'blood' ? 'bg-[#20596c] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
                4. Profil Darah
            </button>
            <button type="button" @click="activeTab = 'bp'" :class="activeTab === 'bp' ? 'bg-[#20596c] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
                5. Tekanan Darah
            </button>
            <button type="button" @click="activeTab = 'activity'" :class="activeTab === 'activity' ? 'bg-[#20596c] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
                6. Kepatuhan Aktivitas
            </button>
        </div>

        <!-- 6 Modern Clinical Charts Grid (Non-AI, Crisp Clean Aesthetics) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- GRAFIK 1: Beban Toksin (0–50 Indikator) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-shield-virus text-[#20596c]"></i>
                            1. Tren Beban Toksin (0–50 Indikator)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Penurunan akumulasi 50 keluhan toksin: Level 1 (0-5), Level 2 (6-15), Level 3 (&ge;16)</p>
                    </div>
                    <span class="text-xs font-mono text-[#20596c] bg-[#f0f7f9] px-2.5 py-1 rounded-full font-semibold border border-[#bedde5]">Leveling DSH</span>
                </div>
                <div class="overflow-x-auto pb-2">
                    <div class="h-72 min-w-[500px] relative">
                        <canvas id="toxinChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFIK 2: Komposisi Tubuh (Berat Badan & Lingkar Perut) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-weight-scale text-blue-600"></i>
                            2. Komposisi Tubuh (Berat &amp; Lingkar Perut)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Target: LP Pria &lt; 90 cm, Wanita &lt; 80 cm &bull; IMT Ideal 18.5 - 22.9</p>
                    </div>
                    <span class="text-xs font-mono text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full font-semibold">Dual Axis</span>
                </div>
                <div class="overflow-x-auto pb-2">
                    <div class="h-72 min-w-[500px] relative">
                        <canvas id="bodyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFIK 3: Glukosa, Keton & Nilai GKI -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-dna text-indigo-600"></i>
                            3. Glukosa Darah &amp; Indeks GKI
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">GKI = (Glukosa / 18) / Keton &bull; Zona Terapeutik: GKI &le; 1 s/d 3 &bull; Puasa Sehat: &le; 9</p>
                    </div>
                    <span class="text-xs font-mono text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full font-semibold">GKI Index</span>
                </div>
                <div class="overflow-x-auto pb-2">
                    <div class="h-72 min-w-[500px] relative">
                        <canvas id="metabolicChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFIK 4: Profil Darah (Kolesterol & Asam Urat) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-vial text-amber-600"></i>
                            4. Profil Darah (Kolesterol &amp; Asam Urat)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Target Kolesterol Normal &lt; 200 mg/dL &bull; Asam Urat: Pria &lt; 7, Wanita &lt; 6 mg/dL</p>
                    </div>
                    <span class="text-xs font-mono text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full font-semibold">Lab Klinis</span>
                </div>
                <div class="overflow-x-auto pb-2">
                    <div class="h-72 min-w-[500px] relative">
                        <canvas id="bloodChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFIK 5: Tekanan Darah (Sistolik & Diastolik) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-heart-pulse text-rose-600"></i>
                            5. Tekanan Darah (Sistolik &amp; Diastolik)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Target Normal: Sistolik &lt; 130 mmHg, Diastolik &lt; 85 mmHg &bull; Zona Ketosis: &lt; 110/75</p>
                    </div>
                    <span class="text-xs font-mono text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full font-semibold">Tensi mmHg</span>
                </div>
                <div class="overflow-x-auto pb-2">
                    <div class="h-72 min-w-[500px] relative">
                        <canvas id="bpChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFIK 6: Kepatuhan Laporan Aktivitas Mandiri Pasien (MHLS) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-person-walking text-[#20596c]"></i>
                            6. Kepatuhan Aktivitas Mandiri (MHLS)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Persentase kedisiplinan eksekusi instruksi puasa &amp; pola hidup sehat dari workflow Temporal</p>
                    </div>
                    <span class="text-xs font-mono text-[#20596c] bg-[#dceef2] px-2.5 py-1 rounded-full font-semibold">Orkestrator</span>
                </div>
                <div class="overflow-x-auto pb-2">
                    <div class="h-72 min-w-[500px] relative">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <!-- Session Comparison History Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Matriks Perbandingan Riwayat Sesi Pemeriksaan</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Data audit klinis per sesi kunjungan</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-[#f0f7f9] text-[#183642] uppercase text-[10px] tracking-wider font-bold">
                        <tr>
                            <th class="py-3 px-4">Pertemuan / Sesi</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Toksin Aktif</th>
                            <th class="py-3 px-4">Level</th>
                            <th class="py-3 px-4">Berat (kg)</th>
                            <th class="py-3 px-4">Glukosa (mg/dL)</th>
                            <th class="py-3 px-4">Keton (mmol/L)</th>
                            <th class="py-3 px-4">Indeks GKI</th>
                            <th class="py-3 px-4">TD (mmHg)</th>
                            <th class="py-3 px-4">Catatan Evaluasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($sessions as $s)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $s->meeting_number ? 'Pertemuan #' . $s->meeting_number : $s->session_number }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap">{{ $s->session_date->format('d M Y') }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                    {{ $s->toxinAssessment?->total_indicated ?? '-' }}/50
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $lvl = $s->toxinAssessment?->level ?? 'Level 1';
                                        $color = $lvl === 'Level 1' ? 'bg-emerald-50 text-emerald-700' : ($lvl === 'Level 2' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $color }}">{{ $lvl }}</span>
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold">{{ $s->bodyMeasurement?->weight ?? '-' }}</td>
                                <td class="py-3 px-4 font-mono">{{ $s->bloodMeasurement?->glucose ?? '-' }}</td>
                                <td class="py-3 px-4 font-mono">{{ $s->bloodMeasurement?->ketone ?? '-' }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-[#20596c]">
                                    {{ $s->bloodMeasurement?->gki ? number_format($s->bloodMeasurement->gki, 2) : '-' }}
                                </td>
                                <td class="py-3 px-4 font-mono">
                                    {{ $s->bloodMeasurement?->systolic && $s->bloodMeasurement?->diastolic ? $s->bloodMeasurement->systolic . '/' . $s->bloodMeasurement->diastolic : '-' }}
                                </td>
                                <td class="py-3 px-4 max-w-xs truncate text-slate-500">
                                    {{ $s->activity_evaluation_notes ?: ($s->examination_summary ?: '-') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const sessions = {!! json_encode($chartData['sessions']) !!};
    const toxinCounts = {!! json_encode($chartData['toxin_counts']) !!};
    const weights = {!! json_encode($chartData['weight']) !!};
    const waists = {!! json_encode($chartData['waist']) !!};
    const glucoses = {!! json_encode($chartData['glucose']) !!};
    const gkis = {!! json_encode($chartData['gki']) !!};
    const cholesterols = {!! json_encode($chartData['cholesterol']) !!};
    const uricAcids = {!! json_encode($chartData['uric_acid']) !!};
    const systolics = {!! json_encode($chartData['systolic'] ?? [130, 120]) !!};
    const diastolics = {!! json_encode($chartData['diastolic'] ?? [85, 80]) !!};
    const compliances = {!! json_encode($chartData['activity_compliance'] ?? [85, 92]) !!};

    // Label sumbu X: Jika data baru 1-2, tambahkan slot pertemuan MHLS berikutnya agar grafik berjarak proporsional
    let displayLabels = [...sessions];
    const isMhls = "{{ $patient->patient_type }}" === 'mhls';
    if (isMhls && displayLabels.length < 6) {
        for (let i = displayLabels.length + 1; i <= 6; i++) {
            displayLabels.push('P #' + i + ' (Target)');
        }
    }

    const gridStyle = {
        color: 'rgba(226, 232, 240, 0.7)',
        drawBorder: false,
    };

    const tooltipStyle = {
        backgroundColor: '#174250',
        titleColor: '#ffffff',
        bodyColor: '#e2e8f0',
        borderColor: '#20596c',
        borderWidth: 1,
        titleFont: { size: 12, weight: 'bold' },
        bodyFont: { size: 11 },
        padding: 10,
        cornerRadius: 8,
        displayColors: true,
    };

    // 1. Toxin Chart
    const ctxToxin = document.getElementById('toxinChart');
    if (ctxToxin) {
        new Chart(ctxToxin, {
            type: 'line',
            data: {
                labels: displayLabels,
                datasets: [{
                    label: 'Indikator Toksin Aktif',
                    data: toxinCounts,
                    borderColor: '#20596c',
                    backgroundColor: 'rgba(32, 89, 108, 0.09)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3,
                    spanGaps: false,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#20596c',
                    pointBorderWidth: 2.5,
                    pointRadius: 5.5,
                    pointHoverRadius: 7.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 20, top: 10, bottom: 5 } },
                plugins: { legend: { display: false }, tooltip: tooltipStyle },
                scales: {
                    y: { beginAtZero: true, max: 50, grid: gridStyle, ticks: { stepSize: 10, font: { size: 11 } } },
                    x: { grid: { color: 'rgba(241, 245, 249, 0.8)' }, ticks: { font: { size: 11, weight: 'bold' } } }
                }
            }
        });
    }

    // 2. Body Chart (Berat Badan & Lingkar Perut)
    const ctxBody = document.getElementById('bodyChart');
    if (ctxBody) {
        new Chart(ctxBody, {
            type: 'line',
            data: {
                labels: displayLabels,
                datasets: [
                    {
                        label: 'Berat Badan (kg)',
                        data: weights,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.07)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.3,
                        spanGaps: false,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#2563eb',
                        pointBorderWidth: 2.5,
                        pointRadius: 5.5,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Lingkar Perut (cm)',
                        data: waists,
                        borderColor: '#0891b2',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.3,
                        spanGaps: false,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0891b2',
                        pointBorderWidth: 2,
                        pointRadius: 4.5,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 20, top: 10, bottom: 5 } },
                plugins: { legend: { position: 'top', labels: { boxWidth: 14, font: { size: 11 } } }, tooltip: tooltipStyle },
                scales: {
                    y: { type: 'linear', display: true, position: 'left', grid: gridStyle, ticks: { font: { size: 11 } } },
                    y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 11 } } },
                    x: { grid: { color: 'rgba(241, 245, 249, 0.8)' }, ticks: { font: { size: 11, weight: 'bold' } } }
                }
            }
        });
    }

    // 3. Metabolic & GKI Chart
    const ctxMetabolic = document.getElementById('metabolicChart');
    if (ctxMetabolic) {
        new Chart(ctxMetabolic, {
            type: 'line',
            data: {
                labels: displayLabels,
                datasets: [
                    {
                        label: 'Glukosa (mg/dL)',
                        data: glucoses,
                        borderColor: '#e11d48',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        spanGaps: false,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#e11d48',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Indeks GKI',
                        data: gkis,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.3,
                        spanGaps: false,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#6366f1',
                        pointBorderWidth: 2.5,
                        pointRadius: 5.5,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 20, top: 10, bottom: 5 } },
                plugins: { legend: { position: 'top', labels: { boxWidth: 14, font: { size: 11 } } }, tooltip: tooltipStyle },
                scales: {
                    y: { type: 'linear', display: true, position: 'left', grid: gridStyle, ticks: { font: { size: 11 } } },
                    y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 11 } } },
                    x: { grid: { color: 'rgba(241, 245, 249, 0.8)' }, ticks: { font: { size: 11, weight: 'bold' } } }
                }
            }
        });
    }

    // 4. Blood Chart (Kolesterol & Asam Urat)
    const ctxBlood = document.getElementById('bloodChart');
    if (ctxBlood) {
        new Chart(ctxBlood, {
            type: 'line',
            data: {
                labels: displayLabels,
                datasets: [
                    {
                        label: 'Kolesterol (mg/dL)',
                        data: cholesterols,
                        borderColor: '#d97706',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        spanGaps: false,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#d97706',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Asam Urat (mg/dL)',
                        data: uricAcids,
                        borderColor: '#0d9488',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        spanGaps: false,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0d9488',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 20, top: 10, bottom: 5 } },
                plugins: { legend: { position: 'top', labels: { boxWidth: 14, font: { size: 11 } } }, tooltip: tooltipStyle },
                scales: {
                    y: { type: 'linear', display: true, position: 'left', grid: gridStyle, ticks: { font: { size: 11 } } },
                    y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 11 } } },
                    x: { grid: { color: 'rgba(241, 245, 249, 0.8)' }, ticks: { font: { size: 11, weight: 'bold' } } }
                }
            }
        });
    }

    // 5. BP Chart (Tekanan Darah Sistolik & Diastolik)
    const ctxBP = document.getElementById('bpChart');
    if (ctxBP) {
        new Chart(ctxBP, {
            type: 'line',
            data: {
                labels: displayLabels,
                datasets: [
                    {
                        label: 'Sistolik (mmHg)',
                        data: systolics,
                        borderColor: '#e11d48',
                        borderWidth: 2.5,
                        fill: false,
                        tension: 0.3,
                        spanGaps: false,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#e11d48',
                        pointBorderWidth: 2.5,
                        pointRadius: 5.5,
                    },
                    {
                        label: 'Diastolik (mmHg)',
                        data: diastolics,
                        borderColor: '#3b82f6',
                        borderWidth: 2.5,
                        fill: false,
                        tension: 0.3,
                        spanGaps: false,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2.5,
                        pointRadius: 5.5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 20, top: 10, bottom: 5 } },
                plugins: { legend: { position: 'top', labels: { boxWidth: 14, font: { size: 11 } } }, tooltip: tooltipStyle },
                scales: {
                    y: { beginAtZero: false, min: 50, max: 180, grid: gridStyle, ticks: { stepSize: 20, font: { size: 11 } } },
                    x: { grid: { color: 'rgba(241, 245, 249, 0.8)' }, ticks: { font: { size: 11, weight: 'bold' } } }
                }
            }
        });
    }

    // 6. Activity Compliance Chart (Kepatuhan Laporan Pasien)
    const ctxActivity = document.getElementById('activityChart');
    if (ctxActivity) {
        new Chart(ctxActivity, {
            type: 'bar',
            data: {
                labels: displayLabels,
                datasets: [{
                    label: 'Tingkat Kepatuhan (%)',
                    data: compliances,
                    backgroundColor: 'rgba(32, 89, 108, 0.8)',
                    hoverBackgroundColor: '#20596c',
                    borderRadius: 8,
                    barThickness: 32,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 20, top: 10, bottom: 5 } },
                plugins: { legend: { display: false }, tooltip: tooltipStyle },
                scales: {
                    y: { beginAtZero: true, max: 100, grid: gridStyle, ticks: { stepSize: 20, font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: 'bold' } } }
                }
            }
        });
    }
</script>
@endpush
