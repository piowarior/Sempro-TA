package workflow

import "time"

type ScheduleItem struct {
	ID            int64  `json:"id"`
	ScheduledDate string `json:"scheduled_date"` // YYYY-MM-DD
	ReminderTime  string `json:"reminder_time"`  // HH:MM
	Status        string `json:"status"`         // pending, reminder_sent, reported, overdue, cancelled
}

type ActivityPlanPayload struct {
	PlanID        int64          `json:"plan_id"`
	PatientID     int64          `json:"patient_id"`
	PatientName   string         `json:"patient_name"`
	PatientPhone  string         `json:"patient_phone"`
	Name          string         `json:"name"`
	Description   string         `json:"description"`
	StartDate     string         `json:"start_date"`
	EndDate       string         `json:"end_date"`
	FrequencyType string         `json:"frequency_type"`
	ReminderTime  string         `json:"reminder_time"`
	Schedules     []ScheduleItem `json:"schedules"`
}

type PatientReportSignal struct {
	ReportID        int64     `json:"report_id"`
	PlanID          int64     `json:"plan_id"`
	ScheduleID      *int64    `json:"schedule_id"`
	PatientID       int64     `json:"patient_id"`
	ActivityDate    string    `json:"activity_date"`
	ExecutionStatus string    `json:"execution_status"` // 'dilaksanakan' / 'tidak_dilaksanakan'
	PatientNotes    string    `json:"patient_notes"`
	ReportTime      time.Time `json:"report_time"`
}

type UpdatePlanSignal struct {
	PlanID       int64          `json:"plan_id"`
	Name         string         `json:"name"`
	Description  string         `json:"description"`
	StartDate    string         `json:"start_date"`
	EndDate      string         `json:"end_date"`
	ReminderTime string         `json:"reminder_time"`
	Schedules    []ScheduleItem `json:"schedules"`
}

type CancelPlanSignal struct {
	PlanID int64  `json:"plan_id"`
	Reason string `json:"reason"`
}

type WorkflowState struct {
	PlanID          int64                 `json:"plan_id"`
	CurrentSchedule *ScheduleItem         `json:"current_schedule"`
	Status          string                `json:"status"` // RUNNING, COMPLETED, CANCELLED
	ProcessedDays   int                   `json:"processed_days"`
	LastReport      *PatientReportSignal  `json:"last_report"`
	ReportsReceived []PatientReportSignal `json:"reports_received"`
}

