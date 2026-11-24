@extends('layouts.admin')

@section('title', 'Data Pasien')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Data Pasien</h2>
            <p class="text-xs text-slate-500 mt-1">Kelola data pasien Umum dan MHLS (Mind Heart Life Style)</p>
        </div>
        <a href="{{ route('admin.patients.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow transition">
            <i class="fa-solid fa-user-plus mr-2"></i> Tambah Pasien Baru
        </a>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.patients') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    placeholder="Cari nama pasien atau nomor rekam medis...">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition">
                Filter
            </button>
            @if(request('search'))
                <a href="{{ route('admin.patients') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Patients Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">No. Pasien</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Tipe Pasien</th>
                        <th class="py-3 px-4">Jenis Kelamin</th>
                        <th class="py-3 px-4">No. Telepon</th>
                        <th class="py-3 px-4">Sesi Pemeriksaan</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($patients as $patient)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">{{ $patient->patient_number }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $patient->name }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $patient->patient_type === 'mhls' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ $patient->patient_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">{{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="py-3.5 px-4 text-slate-600 font-mono">{{ $patient->phone_number ?: '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-emerald-600">{{ $patient->sessions->count() }} Sesi</span>
                            </td>
                            <td class="py-3.5 px-4 text-center space-x-1">
                                <a href="{{ route('admin.patients.detail', $patient->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition" title="Lihat Rekam Jejak">
                                    <i class="fa-solid fa-folder-open mr-1.5"></i> Detail
                                </a>
                                <a href="{{ route('admin.examinations.create', $patient->id) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg transition" title="Tambah Pemeriksaan">
                                    <i class="fa-solid fa-plus-circle mr-1.5"></i> Sesi Baru
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Tidak ada data pasien ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($patients->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

