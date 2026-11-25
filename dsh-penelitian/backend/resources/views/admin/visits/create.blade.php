@extends('layouts.admin')

@section('title', 'Buat Jadwal Kunjungan Pasien')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.visits') }}" class="hover:text-[#20596c] transition">Jadwal Kunjungan</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Buat Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Jadwalkan Kunjungan Pasien</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penetapan jadwal kunjungan pertama atau kunjungan lanjutan program MHLS oleh klinik.</p>
        </div>
        <a href="{{ route('admin.visits') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition">
            <i class="fa-solid fa-arrow-left mr-2 text-slate-400"></i> Batal
        </a>
    </div>

    <form action="{{ route('admin.visits.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Pasien *</label>
            <select name="patient_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                <option value="">-- Pilih Pasien Terdaftar --</option>
                @foreach($patients as $p)
                    <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->patient_number }}) - [{{ strtoupper($p->patient_type) }}]
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis Kunjungan *</label>
                <select name="booking_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                    <option value="first_visit" {{ old('booking_type') === 'first_visit' ? 'selected' : '' }}>Kunjungan Pertama (Pemeriksaan Awal)</option>
                    <option value="mhls_followup" {{ old('booking_type', 'mhls_followup') === 'mhls_followup' ? 'selected' : '' }}>Kunjungan Pendampingan MHLS</option>
                    <option value="general_checkup" {{ old('booking_type') === 'general_checkup' ? 'selected' : '' }}>Pemeriksaan Pasien Umum</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pertemuan MHLS (1–12)</label>
                <select name="meeting_number" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                    <option value="">-- Kosongkan jika Pasien Umum --</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ old('meeting_number') == $i ? 'selected' : '' }}>Pertemuan #{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Kunjungan *</label>
                <input type="date" name="visit_date" value="{{ old('visit_date', date('Y-m-d', strtotime('+1 day'))) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Slot Waktu Kunjungan *</label>
                <select name="time_slot" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">
                    <option value="09:00 - 11:00" {{ old('time_slot') === '09:00 - 11:00' ? 'selected' : '' }}>Pagi (09:00 - 11:00)</option>
                    <option value="11:00 - 13:00" {{ old('time_slot') === '11:00 - 13:00' ? 'selected' : '' }}>Siang (11:00 - 13:00)</option>
                    <option value="14:00 - 16:00" {{ old('time_slot') === '14:00 - 16:00' ? 'selected' : '' }}>Sore (14:00 - 16:00)</option>
                    <option value="16:00 - 18:00" {{ old('time_slot') === '16:00 - 18:00' ? 'selected' : '' }}>Petang (16:00 - 18:00)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Khusus Petugas</label>
            <textarea name="staff_notes" rows="3" placeholder="Instruksi kedatangan, persiapan puasa sebelum cek darah, dsb..."
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#20596c] focus:bg-white focus:outline-none">{{ old('staff_notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('admin.visits') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 dsh-btn-primary text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Simpan Jadwal Kunjungan</span>
            </button>
        </div>
    </form>
</div>
@endsection
