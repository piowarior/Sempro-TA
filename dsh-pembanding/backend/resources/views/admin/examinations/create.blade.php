@extends('layouts.admin')

@section('title', 'Pencatatan Sesi Pemeriksaan - ' . $patient->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.patients.detail', $patient->id) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Detail Pasien
                </a>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mt-1">Pencatatan Sesi Pemeriksaan Pasien</h2>
            <p class="text-xs text-slate-500">Pasien: <strong class="text-slate-700">{{ $patient->name }}</strong> ({{ $patient->patient_number }})</p>
        </div>
    </div>

    <form action="{{ route('admin.examinations.store', $patient->id) }}" method="POST" id="examForm" class="space-y-6">
        @csrf

        <!-- Sesi Info -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider text-emerald-700 flex items-center">
                <i class="fa-solid fa-calendar-day mr-2"></i> Informasi Sesi Pemeriksaan
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Sesi *</label>
                    <input type="text" name="session_number" value="{{ old('session_number', $nextSessionNum) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Pemeriksaan *</label>
                    <input type="date" name="session_date" value="{{ old('session_date', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Petugas</label>
                <textarea name="staff_notes" rows="2" placeholder="Catatan observasi atau keluhan pasien..."
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">{{ old('staff_notes') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Ringkasan Evaluasi Perkembangan</label>
                <textarea name="examination_summary" rows="2" placeholder="Ringkasan hasil evaluasi dan rekomendasi program..."
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">{{ old('examination_summary') }}</textarea>
            </div>
        </div>

        <!-- 50 Indikator Toxin Assessment Section -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-slate-100 gap-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-emerald-600"></i>
                        Formulir 50 Indikator Toxin Assessment
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Centang indikator yang dialami atau terindikasi pada pasien saat evaluasi</p>
                </div>

                <!-- Dynamic Real-time Counter & Classification Level Badge -->
                <div class="flex items-center space-x-3 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200">
                    <div class="text-right">
                        <div class="text-xs text-slate-500">Terindikasi: <strong id="indicatedCount" class="font-mono text-emerald-700 text-sm">0</strong> / 50</div>
                    </div>
                    <div id="levelBadge" class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                        Level 1
                    </div>
                </div>
            </div>

            <!-- Disclaimer alert as required by konsep.md -->
            <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-3 text-[11px] text-amber-800 flex items-start space-x-2">
                <i class="fa-solid fa-circle-info text-amber-600 mt-0.5"></i>
                <span>Klasifikasi level ini merupakan standarisasi internal Klinik DSH untuk monitoring berkala, <strong>bukan merupakan diagnosis medis otomatis</strong>.</span>
            </div>

            <!-- 50 Checkbox Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 max-h-[460px] overflow-y-auto pr-2 border border-slate-100 p-3 rounded-xl bg-slate-50/50">
                @foreach($indicators as $num => $name)
                    <label class="flex items-start space-x-2.5 p-2 rounded-lg hover:bg-white hover:shadow-xs transition border border-transparent hover:border-slate-200 cursor-pointer">
                        <input type="checkbox" name="indicated_numbers[]" value="{{ $num }}"
                            class="indicator-checkbox mt-0.5 text-emerald-600 rounded focus:ring-emerald-500"
                            onchange="updateToxinLevel()">
                        <span class="text-xs text-slate-700 leading-snug">
                            <strong class="font-mono text-slate-400 mr-1">#{{ str_pad($num, 2, '0', STR_PAD_LEFT) }}</strong>
                            {{ $name }}
                        </span>
                    </label>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Khusus Toxin Assessment</label>
                <input type="text" name="toxin_notes" placeholder="Catatan tambahan mengenai keluhan toksin..."
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>
        </div>

        <!-- Pemeriksaan Parameter Tubuh & Darah / Ketosis (Opsional) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-weight-scale text-blue-600"></i>
                    Pemeriksaan Body & Ketosis (Opsional)
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Nilai parameter yang tidak diperiksa dapat dibiarkan kosong (tidak otomatis nol)</p>
            </div>

            <!-- Parameter Tubuh -->
            <div>
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-3">1. Parameter Komposisi Tubuh</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Berat Badan (kg)</label>
                        <input type="number" step="0.1" name="weight" id="weightInput" oninput="calculateBMI()" placeholder="Contoh: 75.5"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">BMI (Auto/Manual)</label>
                        <input type="number" step="0.1" name="bmi" id="bmiInput" placeholder="Contoh: 24.5"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Lingkar Pinggang (cm)</label>
                        <input type="number" step="0.1" name="waist_circumference" placeholder="Contoh: 88.0"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Body Fat (%)</label>
                        <input type="number" step="0.1" name="body_fat" placeholder="Contoh: 28.5"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Total Water (%)</label>
                        <input type="number" step="0.1" name="water" placeholder="Contoh: 52.0"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Massa Otot (kg)</label>
                        <input type="number" step="0.1" name="muscle_mass" placeholder="Contoh: 51.2"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Visceral Fat</label>
                        <input type="number" step="0.5" name="visceral_fat" placeholder="Contoh: 9.0"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Suhu Tubuh (°C)</label>
                        <input type="number" step="0.1" name="body_temperature" placeholder="Contoh: 36.5"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Parameter Darah & Ketosis -->
            <div class="pt-3 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-3">2. Parameter Darah & Ketosis</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Tekanan Darah (mmHg)</label>
                        <input type="text" name="blood_pressure" placeholder="120/80"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Heart Rate (bpm)</label>
                        <input type="number" name="heart_rate" placeholder="75"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Glukosa Puasa (mg/dL)</label>
                        <input type="number" step="1" name="glucose" placeholder="95"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Ketone Darah (mmol/L)</label>
                        <input type="number" step="0.1" name="ketone" placeholder="0.8"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Kolesterol Total (mg/dL)</label>
                        <input type="number" step="1" name="cholesterol" placeholder="190"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Asam Urat (mg/dL)</label>
                        <input type="number" step="0.1" name="uric_acid" placeholder="6.2"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Hemoglobin (g/dL)</label>
                        <input type="number" step="0.1" name="hemoglobin" placeholder="14.2"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.patients.detail', $patient->id) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-7 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Sesi Pemeriksaan</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function updateToxinLevel() {
        const checkboxes = document.querySelectorAll('.indicator-checkbox');
        let count = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) count++;
        });

        document.getElementById('indicatedCount').textContent = count;
        const badge = document.getElementById('levelBadge');

        if (count <= 5) {
            badge.textContent = 'Level 1';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm';
        } else if (count <= 15) {
            badge.textContent = 'Level 2';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm';
        } else {
            badge.textContent = 'Level 3';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 shadow-sm';
        }
    }

    // Call initially
    updateToxinLevel();
</script>
@endpush
@endsection

