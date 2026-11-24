@extends('layouts.admin')

@section('title', 'Detail Pasien - ' . $patient->name)

@section('content')
<div class="space-y-6">
    <!-- Patient Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center text-2xl font-bold shadow-md">
                {{ strtoupper(substr($patient->name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-800">{{ $patient->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $patient->patient_type === 'mhls' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700' }}">
                        {{ $patient->patient_type }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded font-mono font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $patient->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    No. Rekam: <span class="font-mono font-bold text-slate-700">{{ $patient->patient_number }}</span> &bull; 
                    Lahir: {{ $patient->birth_date->format('d M Y') }} ({{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}) &bull;
                    Telp: {{ $patient->phone_number ?: '-' }}
                </p>
                <p class="text-xs text-slate-400 mt-0.5">Alamat: {{ $patient->address ?: '-' }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.examinations.create', $patient->id) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow transition">
                <i class="fa-solid fa-notes-medical mr-2"></i> Tambah Pemeriksaan
            </a>
            <a href="{{ route('admin.examinations.charts', $patient->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow transition">
                <i class="fa-solid fa-chart-line mr-2"></i> Grafik Perkembangan
            </a>
            @if($patient->patient_type === 'mhls')
                <a href="{{ route('admin.activities.create') }}?patient_id={{ $patient->id }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow transition">
                    <i class="fa-solid fa-person-walking mr-2"></i> Instruksi MHLS
                </a>
            @endif
        </div>
    </div>

    <!-- 2 Column: Examination Sessions & Active Activities -->
    <div class="space-y-6">
        <!-- Riwayat Pemeriksaan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                    Riwayat Sesi Pemeriksaan ({{ $patient->sessions->count() }})
                </h3>
                <a href="{{ route('admin.examinations.charts', $patient->id) }}" class="text-xs text-emerald-600 hover:underline font-semibold">
                    Visualisasi Grafik &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">No. Sesi</th>
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">Toxin Assessment</th>
                            <th class="py-2.5 px-3">Berat / BMI</th>
                            <th class="py-2.5 px-3">Glukosa / TD</th>
                            <th class="py-2.5 px-3">Ringkasan Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($patient->sessions as $session)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-mono font-bold text-slate-800">{{ $session->session_number }}</td>
                                <td class="py-3 px-3 text-slate-600">{{ $session->session_date->format('d M Y') }}</td>
                                <td class="py-3 px-3">
                                    @if($session->toxinAssessment)
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $session->toxinAssessment->level === 'Level 1' ? 'bg-emerald-100 text-emerald-800' : ($session->toxinAssessment->level === 'Level 2' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                                {{ $session->toxinAssessment->level }}
                                            </span>
                                            <span class="text-slate-500 font-mono">({{ $session->toxinAssessment->total_indicated }}/50)</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-700 font-mono">
                                    {{ $session->bodyMeasurement?->weight ? $session->bodyMeasurement->weight . ' kg' : '-' }}
                                    @if($session->bodyMeasurement?->bmi)
                                        <span class="text-slate-400 text-[10px]">({{ $session->bodyMeasurement->bmi }})</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-700 font-mono">
                                    {{ $session->bloodMeasurement?->glucose ? $session->bloodMeasurement->glucose . ' mg/dL' : '-' }}
                                    @if($session->bloodMeasurement?->blood_pressure)
                                        <span class="text-slate-400 text-[10px]">({{ $session->bloodMeasurement->blood_pressure }})</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-600 max-w-xs truncate">
                                    {{ $session->examination_summary ?: ($session->staff_notes ?: '-') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">
                                    Belum ada sesi pemeriksaan tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Instruksi Aktivitas Mandiri Pasien (MHLS) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-person-walking text-indigo-600"></i>
                    Instruksi Aktivitas Mandiri Pasien & Temporal Workflow
                </h3>
                <a href="{{ route('admin.activities.create') }}?patient_id={{ $patient->id }}" class="text-xs text-indigo-600 hover:underline font-semibold">
                    + Tambah Instruksi &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">Nama Aktivitas</th>
                            <th class="py-2.5 px-3">Periode</th>
                            <th class="py-2.5 px-3">Frekuensi / Jam</th>
                            <th class="py-2.5 px-3">Status Plan</th>
                            <th class="py-2.5 px-3">Temporal Workflow</th>
                            <th class="py-2.5 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($patient->activityPlans as $plan)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3">
                                    <p class="font-bold text-slate-800">{{ $plan->name }}</p>
                                    <p class="text-slate-400 text-[10px] truncate max-w-xs">{{ $plan->description }}</p>
                                </td>
                                <td class="py-3 px-3 text-slate-600 font-mono">
                                    {{ $plan->start_date->format('d/m/Y') }} - {{ $plan->end_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    <span class="capitalize">{{ $plan->frequency_type }}</span> &bull; 
                                    <span class="font-mono font-bold">{{ substr($plan->reminder_time, 0, 5) }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $plan->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst($plan->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono">
                                    @if($plan->workflowReference)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ $plan->workflowReference->status }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">Menunggu Outbox</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('admin.activities.detail', $plan->id) }}" class="text-xs px-3 py-1 bg-slate-100 hover:bg-slate-200 font-medium rounded-lg text-slate-700 transition">
                                        Detail & Jadwal
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">
                                    Belum ada instruksi aktivitas mandiri yang diberikan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

