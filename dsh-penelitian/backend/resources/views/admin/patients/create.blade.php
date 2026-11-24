@extends('layouts.admin')

@section('title', 'Tambah Pasien Baru')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Pendaftaran Pasien Baru</h2>
            <p class="text-xs text-slate-500 mt-1">Daftarkan akun pasien untuk akses mobile app dan rekam medis DSH</p>
        </div>
        <a href="{{ route('admin.patients') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <form action="{{ route('admin.patients.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No. Rekam Medis / ID Internal *</label>
                    <input type="text" name="patient_number" value="{{ old('patient_number', 'DSH-' . strtoupper(Str::random(4)) . '-' . date('my')) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kategori Pasien *</label>
                    <select name="patient_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                        <option value="mhls" {{ old('patient_type') === 'mhls' ? 'selected' : '' }}>MHLS (Mind Heart Life Style)</option>
                        <option value="umum" {{ old('patient_type') === 'umum' ? 'selected' : '' }}>Umum</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap Pasien *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Ahmad Fauzi"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Lahir *</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', '1990-01-01') }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kelamin *</label>
                    <div class="flex items-center space-x-6 pt-2">
                        <label class="inline-flex items-center text-xs">
                            <input type="radio" name="gender" value="L" {{ old('gender', 'L') === 'L' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 font-medium">Laki-laki</span>
                        </label>
                        <label class="inline-flex items-center text-xs">
                            <input type="radio" name="gender" value="P" {{ old('gender') === 'P' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 font-medium">Perempuan</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No. Telepon / WhatsApp</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Tempat Tinggal</label>
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="Kota / Alamat singkat"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                </div>
            </div>

            <!-- Login Credentials for Patient Mobile App -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 text-emerald-700 flex items-center">
                    <i class="fa-solid fa-mobile-screen-button mr-2"></i> Kredensial Login Pasien (Mobile App)
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Username Login *</label>
                        <input type="text" name="username" value="{{ old('username') }}" required placeholder="Contoh: ahmad123"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password *</label>
                        <input type="password" name="password" required value="pasien123"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.patients') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow transition">
                    Simpan Pasien
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

