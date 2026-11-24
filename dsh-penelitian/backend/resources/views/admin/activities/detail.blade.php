@extends('layouts.admin')

@section('title', 'Detail Aktivitas & Workflow - ' . $plan->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.activities') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Daftar Aktivitas
                </a>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mt-1">{{ $plan->name }}</h2>
            <p class="text-xs text-slate-500">
                Pasien: <a href="{{ route('admin.patients.detail', $plan->patient->id) }}" class="font-bold text-emerald-600 hover:underline">{{ $plan->patient->name }}</a> ({{ $plan->patient->patient_number }})
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($plan->status === 'active')
                <form action="{{ route('admin.activities.cancel', $plan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan instruksi aktivitas mandiri ini? Temporal Workflow akan dihentikan.')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-xl text-xs border border-rose-200 transition">
                        <i class="fa-solid fa-ban mr-1.5"></i> Batalkan Instruksi
                    </button>
                </form>
            @endif
            @if($plan->workflowReference)
                <a href="http://localhost:8088/namespaces/default/workflows/{{ $plan->workflowReference->workflow_id }}" target="_blank"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs shadow transition flex items-center gap-1.5">
                    <i class="fa-solid fa-cubes"></i>
                    <span>Buka di Temporal UI</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- 2 Column: Plan Information & Temporal Orchestration Reference -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1: Detail Instruksi -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-emerald-600"></i> Informasi Instruksi Mandiri
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block mb-0.5">Deskripsi Arahan:</span>
                    <p class="text-slate-800 font-medium leading-relaxed">{{ $plan->description }}</p>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Periode Pelaksanaan:</span>
                    <p class="font-mono font-bold text-slate-800">{{ $plan->start_date->format('d M Y') }} &ndash; {{ $plan->end_date->format('d M Y') }}</p>
                    <span class="text-slate-500 text-[11px]">Frekuensi: {{ ucfirst($plan->frequency_type) }} &bull; Jam Pengingat: {{ substr($plan->reminder_time, 0, 5) }}</span>
                </div>
            </div>

            @if($plan->additional_notes)
                <div class="pt-2 border-t border-slate-100 text-xs">
                    <span class="text-slate-400 block mb-0.5">Catatan Tambahan Petugas:</span>
                    <p class="text-slate-700 italic">{{ $plan->additional_notes }}</p>
                </div>
            @endif
        </div>

        <!-- Col 2: Status Temporal Orchestration -->
        <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-indigo-900/60 pb-3">
                <h3 class="text-xs font-bold text-indigo-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-diagram-project"></i> Temporal Orchestration
                </h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $plan->workflowReference?->status === 'running' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-300' }}">
                    {{ strtoupper($plan->workflowReference?->status ?? 'WAITING OUTBOX') }}
                </span>
            </div>

            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-indigo-400 text-[11px] block">Workflow ID:</span>
                    <span class="font-mono text-slate-200 font-bold break-all">{{ $plan->workflowReference?->workflow_id ?? ('activity-plan-' . $plan->id) }}</span>
                </div>
                <div>
                    <span class="text-indigo-400 text-[11px] block">Workflow Type:</span>
                    <span class="font-mono text-slate-300">ActivityPlanWorkflow</span>
                </div>
                <div>
                    <span class="text-indigo-400 text-[11px] block">Task Queue:</span>
                    <span class="font-mono text-emerald-400 font-bold">activity-plan-queue</span>
                </div>
                @if($plan->workflowReference?->run_id)
                    <div>
                        <span class="text-indigo-400 text-[11px] block">Run ID:</span>
                        <span class="font-mono text-slate-400 text-[10px] break-all">{{ $plan->workflowReference->run_id }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Jadwal Pelaksanaan & Durable Timer Step -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-emerald-600"></i>
                    Rincian Jadwal Harian & Status Durable Timer
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Setiap tanggal dikelola oleh Temporal Durable Timer secara presisi dan tahan restart</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-600">{{ $plan->schedules->count() }} Hari Terjadwal</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                    <tr>
                        <th class="py-2.5 px-3">Tanggal Jadwal</th>
                        <th class="py-2.5 px-3">Jam Pengingat</th>
                        <th class="py-2.5 px-3">Status Pelaksanaan</th>
                        <th class="py-2.5 px-3">Notifikasi Terkirim</th>
                        <th class="py-2.5 px-3">Laporan Pasien (Signal)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($plan->schedules as $sched)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3 font-mono font-bold text-slate-800">
                                {{ $sched->scheduled_date->format('d M Y') }}
                                <span class="text-slate-400 font-sans font-normal text-[10px]">({{ $sched->scheduled_date->translatedFormat('l') }})</span>
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-700">
                                {{ substr($sched->reminder_time, 0, 5) }}
                            </td>
                            <td class="py-3 px-3">
                                @if($sched->status === 'reported')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Sudah Dilaporkan
                                    </span>
                                @elseif($sched->status === 'reminder_sent')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Pengingat Terkirim
                                    </span>
                                @elseif($sched->status === 'overdue')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Batas Waktu Terlewati
                                    </span>
                                @elseif($sched->status === 'cancelled')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Dibatalkan
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Menunggu Jadwal
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-600 font-mono">
                                {{ $sched->reminder_sent_at ? $sched->reminder_sent_at->format('H:i d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-3">
                                @if($sched->report)
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $sched->report->execution_status === 'dilaksanakan' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ ucfirst($sched->report->execution_status) }}
                                        </span>
                                        @if($sched->report->patient_notes)
                                            <span class="text-slate-500 italic text-[11px] truncate max-w-xs">"{{ $sched->report->patient_notes }}"</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Belum ada laporan</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Event Outbox Khusus Plan Ini -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-3">
        <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <i class="fa-solid fa-box-archive text-amber-600"></i>
            Jejak Transactional Outbox Events (EDA Traceability)
        </h3>
        <div class="divide-y divide-slate-100">
            @forelse($outboxEvents as $event)
                <div class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 font-mono text-emerald-700">{{ $event->event_type }}</span>
                            <span class="px-2 py-0.2 rounded text-[10px] font-bold {{ $event->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $event->status }}
                            </span>
                        </div>
                        <p class="text-slate-400 font-mono text-[10px] mt-0.5">Correlation ID: {{ $event->correlation_id }} &bull; Event ID: {{ $event->event_id }}</p>
                    </div>
                    <div class="text-right text-slate-500 text-[11px]">
                        Terjadi: {{ $event->occurred_at->format('H:i:s d-m-Y') }}
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 py-3 text-center">Belum ada event outbox tercatat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

