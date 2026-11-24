<?php

namespace App\Console\Commands;

use App\Models\ActivitySchedule;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollScheduledReminders extends Command
{
    protected $signature = 'reminders:poll-send {--daemon : Jalankan secara continuous loop}';
    protected $description = 'Konvensional Poller: Memeriksa jadwal aktivitas dan mengirimkan pengingat berdasarkan polling database berkala';

    public function handle()
    {
        $isDaemon = $this->option('daemon');
        $this->info("Traditional DB Poller Scheduler running (Daemon: " . ($isDaemon ? "YES" : "NO") . ")...");

        do {
            $this->processDueSchedules();

            if ($isDaemon) {
                sleep(10); // Polling interval 10 detik untuk demonstrasi
            }
        } while ($isDaemon);

        return Command::SUCCESS;
    }

    protected function processDueSchedules(): void
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        // Polling database: Mencari schedule hari ini yang pending dan waktu reminder <= waktu sekarang
        $dueSchedules = ActivitySchedule::with(['activityPlan.patient'])
            ->where('scheduled_date', '<=', $today)
            ->where('status', 'pending')
            ->where('reminder_time', '<=', $currentTime)
            ->limit(50)
            ->get();

        if ($dueSchedules->isEmpty()) {
            return;
        }

        $this->info("[" . Carbon::now()->format('H:i:s') . "] Menemukan {$dueSchedules->count()} jadwal yang jatuh tempo.");

        foreach ($dueSchedules as $schedule) {
            $plan = $schedule->activityPlan;
            $patient = $plan?->patient;

            if (!$patient) {
                continue;
            }

            // Simulasi pengiriman pengingat via NotificationLog
            NotificationLog::create([
                'activity_plan_id' => $plan->id,
                'patient_id' => $patient->id,
                'notification_type' => 'reminder',
                'recipient' => $patient->phone_number ?: 'pasien@dsh.local',
                'message' => "Halo {$patient->name}, saatnya melakukan aktivitas mandiri: {$plan->name} ({$plan->reminder_time})",
                'sent_at' => Carbon::now(),
                'delivery_status' => 'success',
                'provider_response' => json_encode(['method' => 'cron_polling', 'status' => 'delivered']),
            ]);

            // Update status secara langsung di database (Konvensional CRUD)
            $schedule->update([
                'status' => 'reminded',
                'reminder_sent_at' => Carbon::now(),
            ]);

            $this->line("  -> Pengingat terkirim ke {$patient->name} untuk rencana #{$plan->id}");
        }
    }
}

