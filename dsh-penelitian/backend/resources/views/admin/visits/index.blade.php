@extends('layouts.admin')

@section('title', 'Pengelolaan Jadwal Kunjungan & Booking Pasien')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Modul 2</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Jadwal Kunjungan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengelolaan Kunjungan & Booking Pasien</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola jadwal kunjungan pertama klinik dan konfirmasi pengajuan 12 pertemuan program MHLS dari aplikasi mobile.</p>
        </div>

        <a href="{{ route('admin.visits.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl text-white text-xs font-semibold shadow-sm transition dsh-btn-primary">
            <i class="fa-solid fa-calendar-plus mr-2"></i> Buat Jadwal Kunjungan
        </a>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 text-xs mb-1.5">
                <span class="font-medium">Menunggu Konfirmasi</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-slate-900">{{ $pendingCount }}</span>
                <span class="text-xs text-slate-500">pengajuan dari mobile app</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 text-xs mb-1.5">
                <span class="font-medium">Jadwal Terkonfirmasi</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-check text-xs"></i>
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-slate-900">{{ $confirmedCount }}</span>
                <span class="text-xs text-slate-500">siap berkunjung</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 text-xs mb-1.5">
                <span class="font-medium">Kunjungan Selesai</span>
                <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#f0f7f9;color:#20596c;">
                    <i class="fa-solid fa-clipboard-check text-xs"></i>
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-slate-900">{{ $completedCount }}</span>
                <span class="text-xs text-slate-500">telah diperiksa</span>
            </div>
        </div>
    </div>

    <!-- Filter Status Tabs -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-2 shadow-sm flex flex-wrap gap-1.5 text-xs font-semibold">
        <a href="{{ route('admin.visits') }}" class="px-3.5 py-1.5 rounded-xl transition" style="{{ !request('status') ? 'background:#20596c;color:#fff;' : 'color:#475569;' }}" onmouseover="if(!this.style.background.includes('#20596c'))this.style.background='#f1f5f9'" onmouseout="if(!this.style.background.includes('#20596c'))this.style.background=''">
            Semua ({{ $visits->total() }})
        </a>
        <a href="{{ route('admin.visits', ['status' => 'pending_confirmation']) }}" class="px-3.5 py-1.5 rounded-xl transition {{ request('status') === 'pending_confirmation' ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            Perlu Konfirmasi ({{ $pendingCount }})
        </a>
        <a href="{{ route('admin.visits', ['status' => 'confirmed']) }}" class="px-3.5 py-1.5 rounded-xl transition {{ request('status') === 'confirmed' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            Terkonfirmasi ({{ $confirmedCount }})
        </a>
        <a href="{{ route('admin.visits', ['status' => 'completed']) }}" class="px-3.5 py-1.5 rounded-xl transition" style="{{ request('status') === 'completed' ? 'background:#20596c;color:#fff;' : 'color:#475569;' }}" onmouseover="if(!this.style.background.includes('#20596c'))this.style.background='#f1f5f9'" onmouseout="if(!this.style.background.includes('#20596c'))this.style.background=''">
            Selesai ({{ $completedCount }})
        </a>
    </div>

    <!-- Table of Visits -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 font-semibold">
                        <th class="py-3.5 px-4">Pasien</th>
                        <th class="py-3.5 px-4">Program / Pertemuan</th>
                        <th class="py-3.5 px-4">Tanggal Kunjungan</th>
                        <th class="py-3.5 px-4">Waktu / Slot</th>
                        <th class="py-3.5 px-4">Asal Pengajuan</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($visits as $v)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $v->patient->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $v->patient->patient_number }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($v->meeting_number)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold text-[11px]" style="background:#f0f7f9;color:#20596c;">
                                    MHLS Pertemuan #{{ $v->meeting_number }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px]">
                                    Pasien Umum
                                </span>
                            @endif
                            <div class="text-[10px] text-slate-400 mt-0.5 capitalize">{{ str_replace('_', ' ', $v->booking_type) }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">
                            {{ $v->visit_date->format('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            <span class="font-mono">{{ $v->time_slot }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($v->requested_by === 'patient_app')
                                <span class="inline-flex items-center gap-1 text-[11px] text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md font-medium">
                                    <i class="fa-solid fa-mobile-screen text-[10px]"></i> Mobile App
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md font-medium">
                                    <i class="fa-solid fa-clinic-medical text-[10px]"></i> Klinik
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($v->status === 'pending_confirmation')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fa-solid fa-clock mr-1 text-[10px]"></i> Menunggu Konfirmasi
                                </span>
                            @elseif($v->status === 'confirmed')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fa-solid fa-check mr-1 text-[10px]"></i> Terkonfirmasi
                                </span>
                            @elseif($v->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                    Dibatalkan
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($v->status === 'pending_confirmation')
                                    <form action="{{ route('admin.visits.confirm', $v->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-lg text-white font-semibold text-[11px] shadow-xs transition dsh-btn-primary">
                                            Konfirmasi
                                        </button>
                                    </form>
                                @endif

                                @if($v->status === 'confirmed')
                                    <a href="{{ route('admin.examinations.create', $v->patient_id) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg font-semibold text-[11px] transition" style="background:#f0f7f9;color:#20596c;" onmouseover="this.style.background='#dceef2'" onmouseout="this.style.background='#f0f7f9'">
                                        <i class="fa-solid fa-stethoscope mr-1"></i> Periksa
                                    </a>
                                @endif

                                @if($v->status !== 'completed' && $v->status !== 'cancelled')
                                    <form action="{{ route('admin.visits.cancel', $v->id) }}" method="POST" onsubmit="return confirm('Batalkan jadwal kunjungan ini?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Batalkan">
                                            <i class="fa-solid fa-ban text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            Belum ada jadwal kunjungan tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visits->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $visits->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
