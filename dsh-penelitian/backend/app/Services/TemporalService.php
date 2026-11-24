<?php

namespace App\Services;

use App\Models\WorkflowReference;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TemporalService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.temporal.worker_url', env('TEMPORAL_WORKER_URL', 'http://temporal-worker:9000'));
    }

    /**
     * Start the ActivityPlanWorkflow for a given activity plan
     */
    public function startActivityPlanWorkflow(array $planPayload): array
    {
        $planId = $planPayload['plan_id'];
        $workflowId = 'activity-plan-' . $planId;

        // Idempotency check: don't start if already running
        $existing = WorkflowReference::where('workflow_id', $workflowId)
            ->where('status', 'running')
            ->first();

        if ($existing) {
            Log::info("Temporal workflow already running for plan #{$planId}, workflowId={$workflowId}");
            return ['status' => 'already_running', 'workflow_id' => $workflowId, 'run_id' => $existing->run_id];
        }

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/api/workflows/start", [
                'workflow_id' => $workflowId,
                'plan_id' => $planId,
                'payload' => $planPayload,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $runId = $data['run_id'] ?? null;

                WorkflowReference::updateOrCreate(
                    ['workflow_id' => $workflowId],
                    [
                        'activity_plan_id' => $planId,
                        'run_id' => $runId,
                        'workflow_type' => 'ActivityPlanWorkflow',
                        'status' => 'running',
                        'started_at' => now(),
                    ]
                );

                return ['status' => 'started', 'workflow_id' => $workflowId, 'run_id' => $runId];
            }

            throw new \Exception("Temporal worker start error: " . $response->body());
        } catch (\Exception $e) {
            Log::error("Failed to start Temporal workflow: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send Patient Reported Signal to active workflow
     */
    public function signalPatientReport(int $planId, array $reportPayload): bool
    {
        $workflowId = 'activity-plan-' . $planId;

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/api/workflows/signal/report", [
                'workflow_id' => $workflowId,
                'signal_name' => 'patient-reported-signal',
                'report' => $reportPayload,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to signal Temporal workflow: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Update Plan Signal to active workflow
     */
    public function signalUpdatePlan(int $planId, array $updatedPlanPayload): bool
    {
        $workflowId = 'activity-plan-' . $planId;

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/api/workflows/signal/update", [
                'workflow_id' => $workflowId,
                'signal_name' => 'update-plan-signal',
                'plan' => $updatedPlanPayload,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to signal update to Temporal workflow: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Cancel Plan Signal or cancel workflow
     */
    public function cancelWorkflow(int $planId, string $reason): bool
    {
        $workflowId = 'activity-plan-' . $planId;

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/api/workflows/cancel", [
                'workflow_id' => $workflowId,
                'reason' => $reason,
            ]);

            if ($response->successful()) {
                WorkflowReference::where('workflow_id', $workflowId)
                    ->update(['status' => 'cancelled', 'completed_at' => now()]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Failed to cancel Temporal workflow: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Describe workflow execution status from Temporal
     */
    public function getWorkflowStatus(string $workflowId): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/workflows/{$workflowId}");
            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

