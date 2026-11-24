<?php

namespace App\Console\Commands;

use App\Models\OutboxEvent;
use App\Services\TemporalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DispatchOutboxEvents extends Command
{
    protected $signature = 'outbox:dispatch {--daemon : Run as continuous background daemon} {--sleep=2 : Sleep seconds between polls}';
    protected $description = 'Polls transactional outbox table and dispatches pending domain events';

    public function handle(TemporalService $temporalService): int
    {
        $isDaemon = $this->option('daemon');
        $sleepSeconds = (int) $this->option('sleep');

        $this->info("Transactional Outbox Dispatcher started. Daemon: " . ($isDaemon ? "YES" : "NO"));

        do {
            $processedCount = $this->processPendingEvents($temporalService);

            if ($processedCount > 0) {
                $this->info("Processed {$processedCount} outbox events.");
            }

            if ($isDaemon) {
                sleep($sleepSeconds);
            }
        } while ($isDaemon);

        return Command::SUCCESS;
    }

    protected function processPendingEvents(TemporalService $temporalService): int
    {
        // Select pending events with limit
        $events = OutboxEvent::where('status', 'pending')
            ->where('retry_count', '<', 5)
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get();

        if ($events->isEmpty()) {
            return 0;
        }

        $count = 0;
        foreach ($events as $event) {
            $count++;
            $this->info("Dispatching Event #{$event->id}: [{$event->event_type}] Aggregate: {$event->aggregate_id}");

            try {
                $this->dispatchDomainEvent($event, $temporalService);
                $event->markAsPublished();
                $this->info("  -> SUCCESS: Event #{$event->id} marked as published.");
            } catch (\Exception $e) {
                $this->error("  -> FAILED: Event #{$event->id} error: " . $e->getMessage());
                $event->markAsFailed($e->getMessage());
            }
        }

        return $count;
    }

    protected function dispatchDomainEvent(OutboxEvent $event, TemporalService $temporalService): void
    {
        $payload = $event->payload;

        switch ($event->event_type) {
            case 'ActivityPlanCreated':
                $temporalService->startActivityPlanWorkflow($payload);
                break;

            case 'ActivityPlanUpdated':
                $planId = (int) $event->aggregate_id;
                $temporalService->signalUpdatePlan($planId, $payload);
                break;

            case 'ActivityPlanCancelled':
                $planId = (int) $event->aggregate_id;
                $reason = $payload['reason'] ?? 'Cancelled by staff';
                $temporalService->cancelWorkflow($planId, $reason);
                break;

            case 'PatientActivityReported':
                $planId = (int) ($payload['plan_id'] ?? 0);
                if ($planId > 0) {
                    $temporalService->signalPatientReport($planId, $payload);
                }
                break;

            case 'PatientProgressRecorded':
                // Domain notification / audit
                Log::info("Progress session recorded for patient #{$payload['patient_id']}");
                break;

            default:
                Log::warning("Unhandled domain event type: {$event->event_type}");
                break;
        }
    }
}

