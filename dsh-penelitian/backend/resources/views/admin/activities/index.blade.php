@extends('layouts.admin')

@section('title', 'Aktivitas Mandiri Pasien (MHLS)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Aktivitas Mandiri Pasien (MHLS)</h2>
            <p class="text-xs text-slate-500 mt-1">Kelola instruksi gaya hidup, pengingat, dan orkestrasi Temporal Workflow</p>
        </div>
        <a href="{{ route('admin.activities.create') }}" class="inline-flex items-center px-4 py-2 text-white rounded-xl text-xs font-semibold shadow transition dsh-btn-primary">
            <i class="fa-solid fa-plus-circle mr-2"></i> Buat Instruksi Baru
        </a>
    </div>

    <!-- Activity Plans Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Pasien</th>
                        <th class="py-3 px-4">Nama Instruksi</th>
                        <th class="py-3 px-4">Periode</th>
                        <th class="py-3 px-4">Frekuensi & Pengingat</th>
                        <th class="py-3 px-4">Status Plan</th>
                        <th class="py-3 px-4">Temporal Workflow</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.patients.detail', $plan->patient->id) }}" class="font-bold text-slate-900 transition" style="color:#1a3d4a;" onmouseover="this.style.color='#20596c'" onmouseout="this.style.color='#1a3d4a'">
                                    {{ $plan->patient->name }}
                                </a>
                                <p class="text-slate-400 font-mono text-[10px]">{{ $plan->patient->patient_number }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-slate-800">{{ $plan->name }}</p>
                                <p class="text-slate-400 text-[10px] truncate max-w-xs">{{ $plan->description }}</p>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 font-mono">
                                {{ $plan->start_date->format('d/m/Y') }} &ndash; {{ $plan->end_date->format('d/m/Y') }}
                                <span class="text-slate-400 text-[10px]">({{ $plan->schedules->count() }} hari)</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <span class="capitalize font-medium">{{ $plan->frequency_type }}</span> &bull; 
                                <span class="font-mono font-bold text-slate-700">{{ substr($plan->reminder_time, 0, 5) }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $plan->status === 'active' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($plan->status === 'cancelled' ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $plan->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($plan->workflowReference)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fa-solid fa-gear fa-spin mr-1 text-[8px]"></i>
                                        {{ $plan->workflowReference->status }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-mono text-[10px]">Pending Outbox</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.activities.detail', $plan->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition">
                                    <i class="fa-solid fa-eye mr-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada instruksi aktivitas mandiri dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($plans->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $plans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

