@extends('layouts.admin')

@section('title', 'Pencatatan Sesi Pemeriksaan Klinis - ' . $patient->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.patients') }}" class="hover:text-[#20596c] transition">Data Pasien</a>
                <span>/</span>
                <a href="{{ route('admin.patients.detail', $patient->id) }}" class="hover:text-[#20596c] transition">{{ $patient->name }}</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Formulir 5 Kelompok Pemeriksaan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pencatatan Sesi Pemeriksaan Klinis</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Pasien: <strong class="text-slate-800">{{ $patient->name }}</strong> ({{ $patient->patient_number }}) &bull;
                Kategori: <span class="font-semibold uppercase {{ $patient->patient_type === 'mhls' ? 'text-[#20596c] bg-[#dceef2] px-2 py-0.5 rounded-md' : 'text-slate-700' }}">{{ $patient->patient_type }}</span>
            </p>
        </div>
        <a href="{{ route('admin.patients.detail', $patient->id) }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition">
            <i class="fa-solid fa-arrow-left mr-2 text-slate-400"></i> Batal / Kembali
        </a>
    </div>

    <!-- Alert Instruksi Sesuai Konsep -->
    <div class="bg-[#f0f7f9] border border-[#bedde5] rounded-xl p-4 text-xs text-[#1c4d5e] flex items-start gap-3">
        <i class="fa-solid fa-circle-info text-[#20596c] text-sm mt-0.5"></i>
        <div class="leading-relaxed">
            <p class="font-bold text-[#183642]">SOP Pencatatan Pemeriksaan Klinik DSH (5 Kelompok Klinis):</p>
            <p class="mt-0.5 text-slate-600">
                Pemeriksaan klinis mencakup 5 bagian: <strong>1. Gejala/Toksin (50 Indikator)</strong>, <strong>2. Darah</strong>, <strong>3. Tubuh</strong>, <strong>4. Metabolik</strong>, dan <strong>5. Glukosa-Keton & GKI</strong>. Nilai yang tidak diuji dapat dikosongkan (disimpan sebagai null, bukan nol). Instruksi aktivitas mandiri (Modul 5) dikelola terpisah pada modul Aktivitas Mandiri MHLS.
            </p>
        </div>
    </div>

    <form action="{{ route('admin.examinations.store', $patient->id) }}" method="POST" id="examForm" class="space-y-6">
        @csrf

        <!-- Informasi Sesi & Kunjungan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-xs font-bold text-[#20596c] uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#20596c]"></span>
                    Informasi Sesi Kunjungan
                </h3>
                <span class="text-[11px] text-slate-400">Dasar Rekam Medis</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Sesi *</label>
                    <input type="text" name="session_number" value="{{ old('session_number', $nextSessionNum) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pemeriksaan *</label>
                    <input type="date" name="session_date" value="{{ old('session_date', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pertemuan MHLS (1–12)</label>
                    <select name="meeting_number" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                        <option value="">-- Pasien Umum (Bukan MHLS) --</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('meeting_number', $suggestedMeeting) == $i ? 'selected' : '' }}>
                                Pertemuan #{{ $i }} {{ $i == 1 ? '(Awal Program)' : ($i == 12 ? '(Evaluasi Akhir)' : '') }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            @if($activeVisits->count() > 0)
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tautkan dengan Jadwal Booking Kunjungan</label>
                <select name="visit_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                    <option value="">-- Tanpa Tautan Jadwal --</option>
                    @foreach($activeVisits as $v)
                        <option value="{{ $v->id }}">
                            {{ $v->visit_date->format('d M Y') }} ({{ $v->time_slot }}) - Pertemuan #{{ $v->meeting_number ?? 'Umum' }} [{{ strtoupper($v->status) }}]
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Status booking yang dipilih akan otomatis ditandai "Selesai Dilayani (Completed)".</p>
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Observasi Petugas</label>
                    <textarea name="staff_notes" rows="2" placeholder="Kondisi umum pasien, keluhan utama saat datang..."
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">{{ old('staff_notes') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ringkasan Evaluasi Sesi Ini</label>
                    <textarea name="examination_summary" rows="2" placeholder="Kesimpulan tindakan klinis & respon terapi..."
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">{{ old('examination_summary') }}</textarea>
                </div>
            </div>
        </div>

        <!-- KELOMPOK 1: 50 INDIKATOR GEJALA TOKSIN (Modul 3.1) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-slate-100 gap-2">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#dceef2] text-[#20596c] flex items-center justify-center font-bold text-xs">1</span>
                        Evaluasi 50 Indikator Gejala Toksin
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Checklist gejala fisik untuk mengukur beban racun internal tubuh (Ketentuan Internal DSH)</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <span class="text-xs text-slate-500">Terindikasi:</span>
                        <span id="indicatedCount" class="font-bold text-slate-900 ml-1 text-sm">0</span>/50
                    </div>
                    <span id="levelBadge" class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        Level 1
                    </span>
                </div>
            </div>

            <!-- Quick Action Toolbar -->
            <div class="flex items-center justify-between text-xs text-slate-500 pb-2">
                <span class="text-[11px] text-slate-400">Level 1 (0-5), Level 2 (6-15), Level 3 (&ge;16)</span>
                <div class="space-x-2">
                    <button type="button" onclick="selectAllIndicators(false)" class="text-[#20596c] hover:underline text-xs">Reset Semua</button>
                    <button type="button" onclick="selectAllIndicators(true)" class="text-[#20596c] hover:underline text-xs">Pilih Semua</button>
                </div>
            </div>

            <!-- 50 Items Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-96 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200/80">
                @foreach($indicators as $code => $name)
                    <label class="flex items-start space-x-2.5 p-2 rounded-lg hover:bg-white hover:shadow-xs transition border border-transparent hover:border-slate-200 cursor-pointer">
                        <input type="checkbox" name="indicators[{{ $code }}]" value="1"
                            class="indicator-checkbox mt-0.5 h-4 w-4 text-[#20596c] rounded border-slate-300 focus:ring-[#20596c]"
                            onchange="updateToxinLevel()">
                        <span class="text-xs text-slate-700 leading-tight">
                            <span class="font-semibold text-slate-400 text-[10px] mr-1">#{{ $code }}</span>
                            {{ $name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- KELOMPOK 2: PEMERIKSAAN DARAH (Modul 3.2) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-red-100 text-red-700 flex items-center justify-center font-bold text-xs">2</span>
                    Pemeriksaan Darah & Sirkulasi
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Tekanan darah, detak jantung, hemoglobin, kolesterol total, dan asam urat</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sistolik (mmHg)</label>
                    <input type="number" step="1" name="systolic" value="{{ old('systolic') }}" placeholder="Contoh: 120"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Diastolik (mmHg)</label>
                    <input type="number" step="1" name="diastolic" value="{{ old('diastolic') }}" placeholder="Contoh: 80"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Heart Rate (bpm)</label>
                    <input type="number" step="1" name="heart_rate" value="{{ old('heart_rate') }}" placeholder="Contoh: 75"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hemoglobin (g/dL)</label>
                    <input type="number" step="0.1" name="hemoglobin" value="{{ old('hemoglobin') }}" placeholder="Contoh: 14.2"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kolesterol Total (mg/dL)</label>
                    <input type="number" step="1" name="cholesterol" value="{{ old('cholesterol') }}" placeholder="Contoh: 195"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Asam Urat (mg/dL)</label>
                    <input type="number" step="0.1" name="uric_acid" value="{{ old('uric_acid') }}" placeholder="Contoh: 6.2"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
            </div>
        </div>

        <!-- KELOMPOK 3: PENGUKURAN KONDISI TUBUH (Modul 3.3) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">3</span>
                    Pengukuran Kondisi Tubuh & Komposisi
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Berat badan, IMT, lingkar perut, lemak tubuh, massa otot, usia sel, dsb.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Berat Badan (kg)</label>
                    <input type="number" step="0.1" name="weight" id="weightInput" value="{{ old('weight') }}" placeholder="Contoh: 70.5"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none"
                        onchange="calculateBMI()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tinggi Badan (cm)</label>
                    <input type="number" step="0.1" name="height" id="heightInput" value="{{ old('height') }}" placeholder="Contoh: 170"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none"
                        onchange="calculateBMI()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">IMT / BMI</label>
                    <input type="number" step="0.1" name="bmi" id="bmiInput" value="{{ old('bmi') }}" placeholder="Auto atau isi manual"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lingkar Perut (cm)</label>
                    <input type="number" step="0.1" name="waist_circumference" value="{{ old('waist_circumference') }}" placeholder="Contoh: 84"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lemak Tubuh (%)</label>
                    <input type="number" step="0.1" name="body_fat" value="{{ old('body_fat') }}" placeholder="Contoh: 22.4"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kadar Air (%)</label>
                    <input type="number" step="0.1" name="body_water" value="{{ old('body_water') }}" placeholder="Contoh: 55.0"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Massa Otot (kg)</label>
                    <input type="number" step="0.1" name="muscle_mass" value="{{ old('muscle_mass') }}" placeholder="Contoh: 48.5"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rating Fisik</label>
                    <input type="number" step="1" name="physique_rating" value="{{ old('physique_rating') }}" placeholder="1 - 9"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Usia Sel / Metabolisme</label>
                    <input type="number" step="1" name="metabolic_age" value="{{ old('metabolic_age') }}" placeholder="Contoh: 32"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Massa Tulang (kg)</label>
                    <input type="number" step="0.1" name="bone_mass" value="{{ old('bone_mass') }}" placeholder="Contoh: 2.8"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lemak Perut / Visceral</label>
                    <input type="number" step="1" name="visceral_fat" value="{{ old('visceral_fat') }}" placeholder="Contoh: 7"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Suhu Tubuh (&deg;C)</label>
                    <input type="number" step="0.1" name="body_temperature" value="{{ old('body_temperature') }}" placeholder="Contoh: 36.5"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                </div>
            </div>
        </div>

        <!-- KELOMPOK 4: PEMERIKSAAN METABOLIK (Modul 3.4) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">4</span>
                    Pemeriksaan Metabolik
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Catatan khusus fungsi metabolik, keluhan organ eliminasi, dan respon detoksifikasi klinik</p>
            </div>

            <div>
                <textarea name="metabolic_notes" rows="3" placeholder="Catat hasil observasi metabolik sesuai formulir klinik (pencernaan, pola eliminasi BAB/BAK, respon organ eliminasi)..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">{{ old('metabolic_notes') }}</textarea>
            </div>
        </div>

        <!-- KELOMPOK 5: GLUKOSA, KETON & INDEKS GKI (Modul 3.5) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">5</span>
                    Pemeriksaan Glukosa, Keton & Nilai GKI
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Rumus: <code>GKI = (Glukosa / 18) / Keton</code> &bull; Indikator adaptasi terapi ketosis sehat</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Glukosa Darah (mg/dL)</label>
                    <input type="number" step="0.1" name="glucose" id="glucoseInput" value="{{ old('glucose') }}" placeholder="Contoh: 95"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none"
                        oninput="calculateGKI()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keton Darah (mmol/L)</label>
                    <input type="number" step="0.1" name="ketone" id="ketoneInput" value="{{ old('ketone') }}" placeholder="Contoh: 1.2"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none"
                        oninput="calculateGKI()">
                </div>
                <div>
                    <div class="bg-[#f0f7f9] border border-[#bedde5] rounded-xl p-3">
                        <span class="text-[10px] uppercase font-bold text-[#20596c] tracking-wider block mb-0.5">Auto-Calculated GKI:</span>
                        <div id="gkiDisplay" class="text-base font-bold text-[#183642] font-mono">-</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 pb-8">
            <a href="{{ route('admin.patients.detail', $patient->id) }}" class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-sm transition">
                Batal
            </a>
            <button type="submit" class="px-7 py-2.5 bg-[#20596c] hover:bg-[#1c4d5e] text-white rounded-xl text-xs font-semibold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan 5 Kelompok Pemeriksaan</span>
            </button>
        </div>
    </form>
</div>
@endsection

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
            badge.textContent = 'Level 1 (0-5)';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm';
        } else if (count <= 15) {
            badge.textContent = 'Level 2 (6-15)';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm';
        } else {
            badge.textContent = 'Level 3 (≥16)';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 shadow-sm';
        }
    }

    function selectAllIndicators(val) {
        document.querySelectorAll('.indicator-checkbox').forEach(cb => {
            cb.checked = val;
        });
        updateToxinLevel();
    }

    function calculateBMI() {
        const weight = parseFloat(document.getElementById('weightInput').value);
        const height = parseFloat(document.getElementById('heightInput').value);
        const bmiInput = document.getElementById('bmiInput');

        if (!isNaN(weight) && !isNaN(height) && height > 0) {
            const hMeter = height / 100;
            const bmi = (weight / (hMeter * hMeter)).toFixed(1);
            bmiInput.value = bmi;
        }
    }

    function calculateGKI() {
        const glucose = parseFloat(document.getElementById('glucoseInput').value);
        const ketone = parseFloat(document.getElementById('ketoneInput').value);
        const display = document.getElementById('gkiDisplay');

        if (!isNaN(glucose) && !isNaN(ketone) && ketone > 0) {
            const gki = ((glucose / 18) / ketone).toFixed(2);
            let note = '';
            if (gki <= 1.0) note = ' (Highest Ketosis)';
            else if (gki <= 3.0) note = ' (High Ketosis)';
            else if (gki <= 6.0) note = ' (Moderate Ketosis)';
            else if (gki <= 9.0) note = ' (Low Ketosis)';
            else note = ' (Pre-Ketosis / Non-Adaptive)';
            display.textContent = gki + note;
        } else {
            display.textContent = '-';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateGKI();
        updateToxinLevel();
    });
</script>
@endpush
