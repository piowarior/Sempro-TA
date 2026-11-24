@extends('layouts.admin')

@section('title', 'Monitoring EDA & Temporal Orchestration')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Monitoring EDA & Temporal Orchestration</h2>
            <p class="text-xs text-slate-500 mt-1">Pemantauan real-time Transactional Outbox, Durable Workflow Executions, dan Notifikasi</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="http://localhost:8088" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow transition">
                <i class="fa-solid fa-cubes mr-2"></i> Buka Temporal UI Dashboard
            </a>
            <button onclick="window.location.reload()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition" title="Refresh Halaman">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>
    </div>

    <!-- 1. Transactional Outbox Events -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-box-archive text-amber-600"></i>
                    Tabel Transactional Outbox (outbox_events)
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Mencegah dual-write failure: Event disimpan dalam database transaction yang sama dengan data bisnis</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-600">{{ $outboxEvents->count() }} Event Terakhir</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                    <tr>
                        <th class="py-2.5 px-3">Event Type</th>
                        <th class="py-2.5 px-3">Aggregate</th>
                        <th class="py-2.5 px-3">Correlation ID</th>
                        <th class="py-2.5 px-3">Waktu Kejadian</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Detail Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($outboxEvents as $event)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3 font-bold text-emerald-700">{{ $event->event_type }}</td>
                            <td class="py-3 px-3 text-slate-700 font-sans font-medium">{{ $event->aggregate_type }} #{{ $event->aggregate_id }}</td>
                            <td class="py-3 px-3 text-slate-400 text-[10px]">{{ substr($event->correlation_id, 0, 8) }}...{{ substr($event->correlation_id, -6) }}</td>
                            <td class="py-3 px-3 text-slate-500 text-[11px]">{{ $event->occurred_at->format('H:i:s d/m/Y') }}</td>
                            <td class="py-3 px-3 font-sans">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $event->status === 'published' ? 'bg-emerald-100 text-emerald-800' : ($event->status === 'failed' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ strtoupper($event->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-sans">
                                <details class="cursor-pointer">
                                    <summary class="text-xs text-indigo-600 hover:underline font-medium">Lihat JSON</summary>
                                    <pre class="mt-2 p-2 bg-slate-900 text-emerald-400 rounded-lg text-[10px] font-mono overflow-x-auto max-w-sm">{{ json_encode($event->payload, JSON_PRETTY_PRINT) }}</pre>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 font-sans">Belum ada domain events.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Temporal Workflow References -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-diagram-project text-indigo-600"></i>
                    Temporal Workflow References (workflow_references)
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Pemetaan antara instruksi aktivitas klinik dengan instans workflow di Temporal Server</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-600">{{ $workflows->count() }} Workflow</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                    <tr>
                        <th class="py-2.5 px-3">Workflow ID</th>
                        <th class="py-2.5 px-3">Pasien</th>
                        <th class="py-2.5 px-3">Workflow Type</th>
                        <th class="py-2.5 px-3">Waktu Mulai</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-center">Temporal UI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workflows as $wf)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3 font-mono font-bold text-slate-800">{{ $wf->workflow_id }}</td>
                            <td class="py-3 px-3 font-semibold text-slate-900">{{ $wf->plan?->patient?->name ?: '-' }}</td>
                            <td class="py-3 px-3 font-mono text-slate-600">{{ $wf->workflow_type }}</td>
                            <td class="py-3 px-3 text-slate-500 font-mono">{{ $wf->started_at->format('H:i:s d/m/Y') }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $wf->status === 'running' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : ($wf->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $wf->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <a href="http://localhost:8088/namespaces/default/workflows/{{ $wf->workflow_id }}" target="_blank"
                                    class="inline-flex items-center text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                    <span>Buka Execution History</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Belum ada workflow berjalan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Notification Delivery Logs -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-bell text-blue-600"></i>
                    Log Pengiriman Notifikasi Pengingat (notification_logs)
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Riwayat hasil eksekusi SendActivityReminder Activity</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-600">{{ $notifications->count() }} Log</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                    <tr>
                        <th class="py-2.5 px-3">Penerima</th>
                        <th class="py-2.5 px-3">Tipe</th>
                        <th class="py-2.5 px-3">Isi Pesan Notifikasi</th>
                        <th class="py-2.5 px-3">Waktu Kirim</th>
                        <th class="py-2.5 px-3">Status Pengiriman</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($notifications as $notif)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3 font-semibold text-slate-800">{{ $notif->recipient }}</td>
                            <td class="py-3 px-3 uppercase text-[10px] font-bold text-slate-500">{{ $notif->notification_type }}</td>
                            <td class="py-3 px-3 text-slate-700 max-w-sm">{{ $notif->message }}</td>
                            <td class="py-3 px-3 text-slate-500 font-mono text-[11px]">{{ $notif->sent_at ? $notif->sent_at->format('H:i:s d/m/Y') : '-' }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $notif->delivery_status === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ strtoupper($notif->delivery_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">Belum ada pengiriman notifikasi tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

