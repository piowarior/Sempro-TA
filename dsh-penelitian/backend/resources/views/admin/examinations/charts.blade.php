@extends('layouts.admin')

@section('title', 'Grafik Perkembangan Pasien - ' . $patient->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.patients.detail', $patient->id) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Detail Pasien
                </a>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mt-1">Grafik Perkembangan Pasien</h2>
            <p class="text-xs text-slate-500">Pasien: <strong class="text-slate-700">{{ $patient->name }}</strong> ({{ $patient->patient_number }}) &bull; Total {{ $sessions->count() }} Sesi Evaluasi</p>
        </div>

        <a href="{{ route('admin.examinations.create', $patient->id) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow transition">
            <i class="fa-solid fa-plus-circle mr-2"></i> Tambah Sesi Baru
        </a>
    </div>

    @if($sessions->count() < 1)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-12 text-center text-slate-400">
            <i class="fa-solid fa-chart-line text-4xl mb-3 text-slate-300"></i>
            <p class="text-sm font-semibold text-slate-600">Belum Ada Data Pemeriksaan</p>
            <p class="text-xs text-slate-400 mt-1">Catat setidaknya satu sesi pemeriksaan untuk menampilkan visualisasi grafik.</p>
        </div>
    @else
        <!-- Grid Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chart 1: Toxin Assessment Trend -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="font-bold text-slate-800 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-shield-virus text-emerald-600"></i>
                        Grafik Indikator Toxin Assessment (0-50)
                    </h3>
                    <span class="text-[10px] text-slate-400">Target: Level 1 (0-5)</span>
                </div>
                <div class="h-64">
                    <canvas id="toxinChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: Berat Badan & BMI -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="font-bold text-slate-800 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-weight-scale text-blue-600"></i>
                        Grafik Berat Badan (kg) & Lingkar Pinggang (cm)
                    </h3>
                    <span class="text-[10px] text-slate-400">Parameter Fisik</span>
                </div>
                <div class="h-64">
                    <canvas id="bodyChart"></canvas>
                </div>
            </div>

            <!-- Chart 3: Glukosa & Kolesterol -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="font-bold text-slate-800 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-droplet text-rose-600"></i>
                        Grafik Glukosa & Kolesterol (mg/dL)
                    </h3>
                    <span class="text-[10px] text-slate-400">Profil Darah</span>
                </div>
                <div class="h-64">
                    <canvas id="bloodChart"></canvas>
                </div>
            </div>

            <!-- Chart 4: Ketone & Asam Urat -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="font-bold text-slate-800 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-fire text-amber-500"></i>
                        Grafik Ketone Darah (mmol/L) & Asam Urat (mg/dL)
                    </h3>
                    <span class="text-[10px] text-slate-400">Metabolik</span>
                </div>
                <div class="h-64">
                    <canvas id="ketoneChart"></canvas>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    const dates = @json($chartData['dates']);
    const toxinCounts = @json($chartData['toxin_counts']);
    const weights = @json($chartData['weight']);
    const waists = @json($chartData['waist']);
    const glucoses = @json($chartData['glucose']);
    const cholesterols = @json($chartData['cholesterol']);
    const ketones = @json($chartData['ketone']);
    const uricAcids = @json($chartData['uric_acid']);

    // 1. Toxin Assessment Chart
    new Chart(document.getElementById('toxinChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Jumlah Indikator Toksin',
                data: toxinCounts,
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 6,
                pointHoverRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { min: 0, max: 50, title: { display: true, text: 'Jumlah Indikator Terindikasi' } }
            }
        }
    });

    // 2. Body Chart
    new Chart(document.getElementById('bodyChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Berat Badan (kg)',
                    data: weights,
                    borderColor: '#2563eb',
                    tension: 0.3,
                    spanGaps: true,
                    pointRadius: 5
                },
                {
                    label: 'Lingkar Pinggang (cm)',
                    data: waists,
                    borderColor: '#9333ea',
                    tension: 0.3,
                    spanGaps: true,
                    pointRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // 3. Blood Chart (Glucose & Cholesterol)
    new Chart(document.getElementById('bloodChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Glukosa (mg/dL)',
                    data: glucoses,
                    borderColor: '#e11d48',
                    tension: 0.3,
                    spanGaps: true,
                    pointRadius: 5
                },
                {
                    label: 'Kolesterol (mg/dL)',
                    data: cholesterols,
                    borderColor: '#ea580c',
                    tension: 0.3,
                    spanGaps: true,
                    pointRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // 4. Ketone Chart
    new Chart(document.getElementById('ketoneChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Ketone (mmol/L)',
                    data: ketones,
                    borderColor: '#f59e0b',
                    tension: 0.3,
                    spanGaps: true,
                    pointRadius: 5,
                    yAxisID: 'y'
                },
                {
                    label: 'Asam Urat (mg/dL)',
                    data: uricAcids,
                    borderColor: '#64748b',
                    tension: 0.3,
                    spanGaps: true,
                    pointRadius: 5,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Ketone' } },
                y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Asam Urat' } }
            }
        }
    });
</script>
@endpush
@endsection

