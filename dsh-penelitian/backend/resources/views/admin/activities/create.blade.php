@extends('layouts.admin')

@section('title', 'Buat Instruksi Aktivitas Mandiri')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Buat Instruksi Aktivitas Mandiri (MHLS)</h2>
            <p class="text-xs text-slate-500 mt-1">Instruksi akan memicu Transactional Outbox & Workflow Orchestration di Temporal</p>
        </div>
        <a href="{{ route('admin.activities') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <form action="{{ route('admin.activities.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Pilih Pasien MHLS -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Pasien MHLS *</label>
                <select name="patient_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    <option value="">-- Pilih Pasien Terdaftar --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (old('patient_id') == $p->id || request('patient_id') == $p->id) ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->patient_number }}) - Telp: {{ $p->phone_number ?: '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Nama Aktivitas & Template Cepat -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Nama Aktivitas Mandiri *</label>
                    <span class="text-[10px] text-slate-400">Pilih template atau ketik mandiri:</span>
                </div>
                <input type="text" name="name" id="activityNameInput" value="{{ old('name', 'Tidur sebelum pukul 22.00') }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                
                <!-- Quick Template Buttons -->
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <button type="button" onclick="setTemplate('Tidur sebelum pukul 22.00', 'Pasien diminta menjaga waktu istirahat malam dengan tidur sebelum pukul 22.00 untuk proses regenerasi dan detoksifikasi alami.', '21:00')"
                        class="text-[10px] px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition font-medium">
                        🌙 Tidur &lt; 22.00
                    </button>
                    <button type="button" onclick="setTemplate('Pola Makan Real Food', 'Pasien diminta mengonsumsi makanan alami (real food), menghindari makanan ultra-olahan, serta membatasi gula tambahan.', '07:00')"
                        class="text-[10px] px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition font-medium">
                        🥗 Real Food
                    </button>
                    <button type="button" onclick="setTemplate('Puasa Sunnah (Senin & Kamis)', 'Melakukan puasa berkala sesuai arahan terapis untuk memicu autofagi dan pengaturan sensitivitas insulin.', '04:00', 'custom_days', ['Mon', 'Thu'])"
                        class="text-[10px] px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition font-medium">
                        🕌 Puasa Senin & Kamis
                    </button>
                    <button type="button" onclick="setTemplate('Jalan Kaki Ringan 30 Menit', 'Melakukan aktivitas fisik aerobik ringan secara santai setiap pagi hari.', '06:00')"
                        class="text-[10px] px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition font-medium">
                        🚶 Jalan Pagi 30 Menit
                    </button>
                </div>
            </div>

            <!-- Deskripsi Instruksi -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi Instruksi & Arahan *</label>
                <textarea name="description" id="activityDescInput" rows="3" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">{{ old('description', 'Pasien diminta menjaga waktu tidur sebelum pukul 22.00 untuk optimalisasi pemulihan.') }}</textarea>
            </div>

            <!-- Periode Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Mulai *</label>
                    <input type="date" name="start_date" id="startDateInput" value="{{ old('start_date', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Berakhir *</label>
                    <input type="date" name="end_date" id="endDateInput" value="{{ old('end_date', date('Y-m-d', strtotime('+2 days'))) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    <span class="text-[10px] text-slate-400">Contoh: 3 hari (10 - 12 Nov 2026)</span>
                </div>
            </div>

            <!-- Frekuensi & Waktu Pengingat -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Frekuensi Pelaksanaan *</label>
                    <select name="frequency_type" id="freqSelect" onchange="toggleCustomDays()" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                        <option value="daily" {{ old('frequency_type') === 'daily' ? 'selected' : '' }}>Setiap Hari (Daily)</option>
                        <option value="custom_days" {{ old('frequency_type') === 'custom_days' ? 'selected' : '' }}>Hari Tertentu (Custom Days)</option>
                        <option value="weekly" {{ old('frequency_type') === 'weekly' ? 'selected' : '' }}>Mingguan (Weekly)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Waktu Pengingat (HH:MM) *</label>
                    <input type="time" name="reminder_time" id="reminderTimeInput" value="{{ old('reminder_time', '21:00') }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    <span class="text-[10px] text-slate-400">Sistem akan memicu Durable Timer Temporal pada jam ini</span>
                </div>
            </div>

            <!-- Custom Days Checkboxes (hidden if daily) -->
            <div id="customDaysWrapper" class="hidden p-3 bg-slate-50 rounded-xl border border-slate-200">
                <label class="block text-[11px] font-bold text-slate-700 mb-2">Pilih Hari Pelaksanaan:</label>
                <div class="flex flex-wrap gap-3 text-xs">
                    <label class="inline-flex items-center"><input type="checkbox" name="schedule_days[]" value="Mon" class="mr-1.5"> Senin</label>
                    <label class="inline-flex items-center"><input type="checkbox" name="schedule_days[]" value="Tue" class="mr-1.5"> Selasa</label>
                    <label class="inline-flex items-center"><input type="checkbox" name="schedule_days[]" value="Wed" class="mr-1.5"> Rabu</label>
                    <label class="inline-flex items-center"><input type="checkbox" name="schedule_days[]" value="Thu" class="mr-1.5"> Kamis</label>
                    <label class="inline-flex items-center"><input type="checkbox" name="schedule_days[]" value="Fri" class="mr-1.5"> Jumat</label>
                    <label class="inline-flex items-center"><input type="checkbox" name="schedule_days[]" value="Sat" class="mr-1.5"> Sabtu</label>
                    <label class="inline-flex items-center"><input type="checkbox" name="schedule_days[]" value="Sun" class="mr-1.5"> Minggu</label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tambahan untuk Pasien</label>
                <input type="text" name="additional_notes" placeholder="Catatan opsional..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('admin.activities') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-play"></i>
                    <span>Buat Instruksi & Picu Temporal Workflow</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleCustomDays() {
        const val = document.getElementById('freqSelect').value;
        const wrapper = document.getElementById('customDaysWrapper');
        if (val === 'custom_days') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function setTemplate(name, desc, time, freq = 'daily', days = []) {
        document.getElementById('activityNameInput').value = name;
        document.getElementById('activityDescInput').value = desc;
        document.getElementById('reminderTimeInput').value = time;
        document.getElementById('freqSelect').value = freq;
        toggleCustomDays();

        if (days.length > 0) {
            document.querySelectorAll('#customDaysWrapper input[type="checkbox"]').forEach(cb => {
                cb.checked = days.includes(cb.value);
            });
        }
    }
</script>
@endpush
@endsection

