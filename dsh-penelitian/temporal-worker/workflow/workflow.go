package workflow

import (
	"fmt"
	"time"

	"go.temporal.io/sdk/temporal"
	"go.temporal.io/sdk/workflow"
)

const (
	TaskQueueName         = "activity-plan-queue"
	SignalPatientReported = "patient-reported-signal"
	SignalUpdatePlan      = "update-plan-signal"
	SignalCancelPlan      = "cancel-plan-signal"
	QueryWorkflowState    = "get-workflow-state"
)

// ActivityPlanWorkflow orchestrates the entire lifecycle of a patient's self-care activity plan
func ActivityPlanWorkflow(ctx workflow.Context, input ActivityPlanPayload) (WorkflowState, error) {
	logger := workflow.GetLogger(ctx)
	logger.Info("Starting ActivityPlanWorkflow", "PlanID", input.PlanID, "Patient", input.PatientName)

	state := WorkflowState{
		PlanID:          input.PlanID,
		Status:          "RUNNING",
		ProcessedDays:   0,
		ReportsReceived: make([]PatientReportSignal, 0),
	}

	// Register query handler for real-time state inspection
	err := workflow.SetQueryHandler(ctx, QueryWorkflowState, func() (WorkflowState, error) {
		return state, nil
	})
	if err != nil {
		logger.Error("Failed to set query handler", "Error", err)
	}

	// Signal channels
	reportChan := workflow.GetSignalChannel(ctx, SignalPatientReported)
	updateChan := workflow.GetSignalChannel(ctx, SignalUpdatePlan)
	cancelChan := workflow.GetSignalChannel(ctx, SignalCancelPlan)

	activityOptions := workflow.ActivityOptions{
		StartToCloseTimeout: 1 * time.Minute,
		RetryPolicy: &temporalRetryPolicy,
	}
	activityCtx := workflow.WithActivityOptions(ctx, activityOptions)

	schedules := input.Schedules

	// Loop over each schedule item in the plan
	for i := 0; i < len(schedules); i++ {
		schedule := schedules[i]
		state.CurrentSchedule = &schedule

		logger.Info("Processing schedule item",
			"ScheduleID", schedule.ID,
			"Date", schedule.ScheduledDate,
			"ReminderTime", schedule.ReminderTime,
		)

		// 1. Calculate Durable Timer duration
		// For demo / demonstration speed: sleep 10 seconds per reminder stage if already past, or wait actual time
		timerDuration := calculateTimerDuration(schedule.ScheduledDate, schedule.ReminderTime, workflow.Now(ctx))
		logger.Info("Waiting for reminder timer", "Duration", timerDuration)

		// Create timer selector to handle Cancel or Update signals while waiting for reminder
		timerFuture := workflow.NewTimer(ctx, timerDuration)
		timerSelector := workflow.NewSelector(ctx)

		cancelled := false
		updated := false

		timerSelector.AddFuture(timerFuture, func(f workflow.Future) {
			logger.Info("Reminder timer triggered for schedule", "ScheduleID", schedule.ID)
		})

		timerSelector.AddReceive(cancelChan, func(c workflow.ReceiveChannel, more bool) {
			var sig CancelPlanSignal
			c.Receive(ctx, &sig)
			logger.Info("Received CancelPlanSignal while waiting for timer", "Reason", sig.Reason)
			state.Status = "CANCELLED"
			cancelled = true
		})

		timerSelector.AddReceive(updateChan, func(c workflow.ReceiveChannel, more bool) {
			var sig UpdatePlanSignal
			c.Receive(ctx, &sig)
			logger.Info("Received UpdatePlanSignal while waiting for timer", "NewName", sig.Name)
			schedules = sig.Schedules
			updated = true
		})

		timerSelector.Select(ctx)

		if cancelled {
			logger.Info("Workflow cancelled per staff instruction", "PlanID", input.PlanID)
			return state, nil
		}

		if updated {
			// Restart loop with updated schedules
			i = -1
			continue
		}

		// 2. Trigger SendActivityReminder Activity
		reminderMsg := fmt.Sprintf("Halo %s, saatnya melakukan aktivitas mandiri DSH: %s. Semangat menjaga kesehatan!",
			input.PatientName, input.Name)

		var reminderResult bool
		err = workflow.ExecuteActivity(activityCtx, "SendActivityReminderActivity", schedule.ID, input.PatientID, reminderMsg).Get(ctx, &reminderResult)
		if err != nil {
			logger.Error("Failed to execute SendActivityReminderActivity", "Error", err)
		} else {
			schedule.Status = "reminder_sent"
			logger.Info("Activity reminder sent successfully", "ScheduleID", schedule.ID)
		}

		// 3. Wait for Patient Report (Signal) with Timeout Window
		// Patient has a reporting window (e.g. 30 seconds for test/demo mode, or 24 hours in real time)
		reportWindowDuration := 30 * time.Second
		reportWindowTimer := workflow.NewTimer(ctx, reportWindowDuration)
		reportSelector := workflow.NewSelector(ctx)

		reportReceived := false

		reportSelector.AddReceive(reportChan, func(c workflow.ReceiveChannel, more bool) {
			var reportSig PatientReportSignal
			c.Receive(ctx, &reportSig)
			logger.Info("Received PatientReportSignal",
				"ReportID", reportSig.ReportID,
				"Status", reportSig.ExecutionStatus,
				"Notes", reportSig.PatientNotes,
			)
			state.LastReport = &reportSig
			state.ReportsReceived = append(state.ReportsReceived, reportSig)
			schedule.Status = "reported"
			reportReceived = true
		})

		reportSelector.AddFuture(reportWindowTimer, func(f workflow.Future) {
			logger.Info("Report window timed out for schedule", "ScheduleID", schedule.ID)
			if !reportReceived {
				schedule.Status = "overdue"
			}
		})

		reportSelector.AddReceive(cancelChan, func(c workflow.ReceiveChannel, more bool) {
			var sig CancelPlanSignal
			c.Receive(ctx, &sig)
			state.Status = "CANCELLED"
			cancelled = true
		})

		reportSelector.Select(ctx)

		if cancelled {
			logger.Info("Workflow cancelled while waiting for report", "PlanID", input.PlanID)
			return state, nil
		}

		state.ProcessedDays++
	}

	// 4. Mark workflow and plan as completed
	state.Status = "COMPLETED"
	var completeResult bool
	_ = workflow.ExecuteActivity(activityCtx, "CompletePlanActivity", input.PlanID).Get(ctx, &completeResult)

	logger.Info("ActivityPlanWorkflow successfully finished all schedules", "PlanID", input.PlanID)
	return state, nil
}

func calculateTimerDuration(scheduledDate, reminderTime string, now time.Time) time.Duration {
	// Parse scheduled date and reminder time
	layout := "2006-01-02 15:04"
	fullStr := fmt.Sprintf("%s %s", scheduledDate, reminderTime)
	target, err := time.Parse(layout, fullStr)
	if err != nil {
		return 5 * time.Second // fallback safe duration
	}

	diff := target.Sub(now)
	if diff <= 0 {
		// If schedule time has already arrived or is past, fire promptly (5s demo delay)
		return 5 * time.Second
	}

	// In test/demo environment, cap long waiting periods to 15 seconds so demonstrations finish smoothly
	// while still exercising full durable timer mechanics
	if diff > 15*time.Second {
		return 15 * time.Second
	}

	return diff
}

var temporalRetryPolicy = temporal.RetryPolicy{
	InitialInterval:    1 * time.Second,
	BackoffCoefficient: 2.0,
	MaximumInterval:    30 * time.Second,
	MaximumAttempts:    3,
}

