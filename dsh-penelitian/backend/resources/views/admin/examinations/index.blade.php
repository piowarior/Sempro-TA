@extends('layouts.admin')

@section('title', 'Pemeriksaan Klinis Pasien')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pemeriksaan Klinis Pasien</h1>
            <p class="text-sm text-slate-500 mt-1">
                Pencatatan 5 kelompok parameter klinis: Toksin (50 indikator), Antropometri Tubuh, Tanda Vital Darah, Profil Metabolik, dan Glukosa-Keton (GKI).
            </p>
        </div>

        <div class="flex items-center gap-3" x-data="{ openModal: false }">
            <button type="button" @click="openModal = true"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm transition"
                    style="background-color: #20596c;"
                    onmouseover="this.style.backgroundColor='#184452'"
                    onmouseout="this.style.backgroundColor='#20596c'">
                <i class="fa-solid fa-stethoscope mr-2"></i> Catat Pemeriksaan Baru
            </button>

            <!-- Modal Pilih Pasien -->
            <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4" @click.away="openModal = false">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-user-plus text-[#20596c]"></i>
                            Pilih Pasien untuk Pemeriksaan
                        </h3>
                        <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500">
                        Pilih pasien yang akan diperiksa untuk membuka formulir 5 kelompok parameter klinis.
                    </p>
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-700">Nama Pasien / No. RM</label>
                        <select id="selectPatientModal" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#20596c]">
                            <option value="">-- Pilih Salah Satu Pasien --</option>
                            @foreach($patients as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name }} ({{ $pt->patient_number }}) - {{ strtoupper($pt->patient_type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="openModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="button" onclick="goToExamCreate()" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-[#20596c] hover:bg-[#184452] transition">
                            Lanjutkan Formulir &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.examinations.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i class="fa-solid fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#20596c]"
                       placeholder="Cari berdasarkan nama pasien, nomor rekam medis, atau nomor sesi...">
            </div>
            <button type="submit" class="px-5 py-2.5 text-white text-sm font-semibold rounded-xl transition" style="background:#20596c;" onmouseover="this.style.background='#184452'" onmouseout="this.style.background='#20596c'">
                Cari Data
            </button>
            @if(request('search'))
                <a href="{{ route('admin.examinations.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if(isset($pendingExaminations) && $pendingExaminations->count() > 0)
    <!-- Banner Kunjungan Terjadwal yang Belum Diisi Hasil Pemeriksaannya (Tautkan Otomatis) -->
    <div class="bg-[#f0f7f9] border border-[#bedde5] rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full animate-ping bg-amber-500"></span>
                <h3 class="font-bold text-sm text-[#183642] flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-[#20596c]"></i>
                    Jadwal Kunjungan Aktif &mdash; Menunggu / Belum Diisi Pemeriksaan ({{ $pendingExaminations->count() }})
                </h3>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800">
                Otomatis dari Booking Kunjungan
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($pendingExaminations as $pv)
                <div class="bg-white rounded-xl border border-slate-200 p-3.5 flex flex-col justify-between shadow-2xs">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-900">{{ $pv->patient->name }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">
                                Belum Diisi
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 font-mono">
                            {{ $pv->patient->patient_number }} &bull; {{ $pv->visit_date->format('d M Y') }} ({{ $pv->time_slot }})
                        </div>
                        @if($pv->meeting_number)
                            <div class="text-xs font-bold text-[#20596c] mt-1">
                                Program MHLS: Pertemuan #{{ $pv->meeting_number }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400 capitalize">{{ str_replace('_', ' ', $pv->booking_type) }}</span>
                        <a href="{{ route('admin.examinations.create', $pv->patient_id) }}?visit_id={{ $pv->id }}"
                           class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold text-white transition shadow-xs"
                           style="background-color: #20596c;"
                           onmouseover="this.style.backgroundColor='#184452'"
                           onmouseout="this.style.backgroundColor='#20596c'">
                            <i class="fa-solid fa-stethoscope mr-1.5"></i> Isi Hasil Periksa
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Table of Clinical Sessions -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Sesi / Tanggal</th>
                        <th class="py-3.5 px-4">Pasien</th>
                        <th class="py-3.5 px-4">Beban Toksin</th>
                        <th class="py-3.5 px-4">Tubuh (BB / LP)</th>
                        <th class="py-3.5 px-4">Tensi Darah</th>
                        <th class="py-3.5 px-4">Glukosa &amp; GKI</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sessions as $s)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 font-mono text-xs">
                                {{ $s->session_number }}
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                <i class="fa-regular fa-calendar mr-1"></i>{{ $s->session_date->format('d M Y') }}
                            </div>
                            @if($s->meeting_number)
                                <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-md text-[10.5px] font-bold" style="background:#f0f7f9;color:#20596c;">
                                    MHLS Pertemuan #{{ $s->meeting_number }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('admin.patients.detail', $s->patient_id) }}" class="font-bold text-slate-900 hover:text-[#20596c] transition">
                                {{ $s->patient->name }}
                            </a>
                            <div class="text-xs text-slate-500 font-mono">{{ $s->patient->patient_number }}</div>
                            <span class="inline-block mt-0.5 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full {{ $s->patient->patient_type === 'mhls' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $s->patient->patient_type }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($s->toxinAssessment)
                                <div class="font-bold text-slate-800">
                                    {{ $s->toxinAssessment->total_indicated }}/50 Indikator
                                </div>
                                @php
                                    $lvl = $s->toxinAssessment->level ?? 'Level 1';
                                    $lvlColor = $lvl === 'Level 1' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($lvl === 'Level 2' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');
                                @endphp
                                <span class="inline-block text-[10.5px] font-bold px-2 py-0.5 rounded-full border {{ $lvlColor }} mt-0.5">
                                    {{ $lvl }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($s->bodyMeasurement)
                                <div class="font-mono text-xs">
                                    <span class="font-semibold">{{ $s->bodyMeasurement->weight ? number_format($s->bodyMeasurement->weight, 1) . ' kg' : '-' }}</span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5 font-mono">
                                    LP: {{ $s->bodyMeasurement->waist_circumference ? $s->bodyMeasurement->waist_circumference . ' cm' : '-' }}
                                    &bull; BMI: {{ $s->bodyMeasurement->bmi ?: '-' }}
                                </div>
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($s->bloodMeasurement && ($s->bloodMeasurement->systolic || $s->bloodMeasurement->blood_pressure))
                                <div class="font-mono text-xs font-semibold text-slate-900">
                                    {{ $s->bloodMeasurement->systolic ? $s->bloodMeasurement->systolic . '/' . $s->bloodMeasurement->diastolic : $s->bloodMeasurement->blood_pressure }} mmHg
                                </div>
                                @if($s->bloodMeasurement->heart_rate)
                                    <div class="text-xs text-slate-500 mt-0.5">Nadi: {{ $s->bloodMeasurement->heart_rate }} bpm</div>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($s->bloodMeasurement)
                                <div class="font-mono text-xs text-slate-800">
                                    GDS: <span class="font-semibold">{{ $s->bloodMeasurement->glucose ?? '-' }} mg/dL</span>
                                </div>
                                <div class="font-mono text-xs text-slate-600 mt-0.5">
                                    Keton: {{ $s->bloodMeasurement->ketone ?? '-' }} &bull; 
                                    GKI: <strong class="text-[#20596c]">{{ $s->bloodMeasurement->gki ? number_format($s->bloodMeasurement->gki, 2) : '-' }}</strong>
                                </div>
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.examinations.charts', $s->patient_id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition" style="background:#f0f7f9;color:#20596c;" onmouseover="this.style.background='#dceef2'" onmouseout="this.style.background='#f0f7f9'" title="Buka 6 Grafik Perkembangan">
                                    <i class="fa-solid fa-chart-line mr-1.5"></i> 6 Grafik
                                </a>
                                <a href="{{ route('admin.patients.detail', $s->patient_id) }}" class="p-1.5 text-slate-400 hover:text-slate-700 transition" title="Rekam Medis">
                                    <i class="fa-solid fa-folder-open text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                <i class="fa-solid fa-notes-medical text-xl"></i>
                            </div>
                            <p class="font-semibold text-slate-600 text-sm">Belum ada riwayat sesi pemeriksaan klinis.</p>
                            <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Catat Pemeriksaan Baru" di atas untuk memulai pencatatan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function goToExamCreate() {
    const patientId = document.getElementById('selectPatientModal').value;
    if (!patientId) {
        alert('Silakan pilih pasien terlebih dahulu');
        return;
    }
    window.location.href = '/admin/patients/' + patientId + '/examinations/create';
}
</script>
@endsection
