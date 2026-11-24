package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"strings"
	"time"

	"temporal-worker/activity"
	"temporal-worker/workflow"

	"go.temporal.io/sdk/client"
	"go.temporal.io/sdk/worker"
)

type Server struct {
	temporalClient client.Client
	actHandler     *activity.ActivityHandler
}

func main() {
	temporalHost := os.Getenv("TEMPORAL_HOST")
	if temporalHost == "" {
		temporalHost = "temporal:7233"
	}

	log.Printf("[Temporal Worker] Connecting to Temporal Server at %s...", temporalHost)

	var c client.Client
	var err error

	// Retry connecting to Temporal Server with backoff
	for i := 1; i <= 60; i++ {
		c, err = client.Dial(client.Options{
			HostPort:  temporalHost,
			Namespace: "default",
		})
		if err == nil {
			log.Printf("[Temporal Worker] Successfully connected to Temporal Server!")
			break
		}
		log.Printf("[Temporal Worker] Attempt %d/60 failed: %v. Retrying in 3 seconds...", i, err)
		time.Sleep(3 * time.Second)
	}

	if err != nil {
		log.Fatalf("[Temporal Worker] Fatal: Unable to connect to Temporal: %v", err)
	}
	defer c.Close()

	// 1. Create and Start Worker
	w := worker.New(c, workflow.TaskQueueName, worker.Options{})

	actHandler := activity.NewActivityHandler()

	// Register Workflows & Activities
	w.RegisterWorkflow(workflow.ActivityPlanWorkflow)
	w.RegisterActivity(actHandler.SendActivityReminderActivity)
	w.RegisterActivity(actHandler.CompletePlanActivity)

	go func() {
		log.Printf("[Temporal Worker] Starting worker polling on queue: '%s'...", workflow.TaskQueueName)
		if err := w.Run(worker.InterruptCh()); err != nil {
			log.Fatalf("[Temporal Worker] Worker error: %v", err)
		}
	}()

	// 2. Start HTTP Bridge Server for Laravel Integration
	srv := &Server{
		temporalClient: c,
		actHandler:     actHandler,
	}

	http.HandleFunc("/health", srv.handleHealth)
	http.HandleFunc("/api/workflows/start", srv.handleStartWorkflow)
	http.HandleFunc("/api/workflows/signal/report", srv.handleSignalReport)
	http.HandleFunc("/api/workflows/signal/update", srv.handleSignalUpdate)
	http.HandleFunc("/api/workflows/cancel", srv.handleCancelWorkflow)
	http.HandleFunc("/api/workflows/", srv.handleGetWorkflow)

	port := os.Getenv("PORT")
	if port == "" {
		port = "9000"
	}
	log.Printf("[Temporal Worker Bridge] HTTP REST Server listening on :%s...", port)
	if err := http.ListenAndServe(":"+port, nil); err != nil {
		log.Fatalf("HTTP server failed: %v", err)
	}
}

func (s *Server) handleHealth(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{"status": "ok", "service": "temporal-worker-bridge"})
}

func (s *Server) handleStartWorkflow(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req struct {
		WorkflowID string                       `json:"workflow_id"`
		PlanID     int64                        `json:"plan_id"`
		Payload    workflow.ActivityPlanPayload `json:"payload"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, fmt.Sprintf("Invalid JSON: %v", err), http.StatusBadRequest)
		return
	}

	workflowOptions := client.StartWorkflowOptions{
		ID:        req.WorkflowID,
		TaskQueue: workflow.TaskQueueName,
	}

	we, err := s.temporalClient.ExecuteWorkflow(context.Background(), workflowOptions, workflow.ActivityPlanWorkflow, req.Payload)
	if err != nil {
		log.Printf("[Bridge Error] Failed to execute workflow %s: %v", req.WorkflowID, err)
		http.Error(w, fmt.Sprintf("ExecuteWorkflow failed: %v", err), http.StatusInternalServerError)
		return
	}

	log.Printf("[Bridge Success] Started workflow ID: %s, RunID: %s", we.GetID(), we.GetRunID())
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]string{
		"workflow_id": we.GetID(),
		"run_id":      we.GetRunID(),
		"status":      "RUNNING",
	})
}

func (s *Server) handleSignalReport(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req struct {
		WorkflowID string                       `json:"workflow_id"`
		Report     workflow.PatientReportSignal `json:"report"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	err := s.temporalClient.SignalWorkflow(context.Background(), req.WorkflowID, "", workflow.SignalPatientReported, req.Report)
	if err != nil {
		log.Printf("[Bridge Error] Failed to signal report to %s: %v", req.WorkflowID, err)
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	log.Printf("[Bridge Success] Delivered patient-reported-signal to workflow %s", req.WorkflowID)
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]bool{"signaled": true})
}

func (s *Server) handleSignalUpdate(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req struct {
		WorkflowID string                    `json:"workflow_id"`
		Plan       workflow.UpdatePlanSignal `json:"plan"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	err := s.temporalClient.SignalWorkflow(context.Background(), req.WorkflowID, "", workflow.SignalUpdatePlan, req.Plan)
	if err != nil {
		log.Printf("[Bridge Error] Failed to signal update to %s: %v", req.WorkflowID, err)
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	log.Printf("[Bridge Success] Delivered update-plan-signal to workflow %s", req.WorkflowID)
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]bool{"signaled": true})
}

func (s *Server) handleCancelWorkflow(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req struct {
		WorkflowID string `json:"workflow_id"`
		Reason     string `json:"reason"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	cancelSig := workflow.CancelPlanSignal{
		Reason: req.Reason,
	}

	err := s.temporalClient.SignalWorkflow(context.Background(), req.WorkflowID, "", workflow.SignalCancelPlan, cancelSig)
	if err != nil {
		// Fallback to Temporal client CancelWorkflow
		_ = s.temporalClient.CancelWorkflow(context.Background(), req.WorkflowID, "")
	}

	log.Printf("[Bridge Success] Workflow %s cancel requested with reason: %s", req.WorkflowID, req.Reason)
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]bool{"cancelled": true})
}

func (s *Server) handleGetWorkflow(w http.ResponseWriter, r *http.Request) {
	workflowID := strings.TrimPrefix(r.URL.Path, "/api/workflows/")
	if workflowID == "" {
		http.Error(w, "Workflow ID is required", http.StatusBadRequest)
		return
	}

	// 1. Try querying workflow state directly
	queryResp, err := s.temporalClient.QueryWorkflow(context.Background(), workflowID, "", workflow.QueryWorkflowState)
	if err == nil {
		var state workflow.WorkflowState
		if err := queryResp.Get(&state); err == nil {
			w.Header().Set("Content-Type", "application/json")
			json.NewEncoder(w).Encode(state)
			return
		}
	}

	// 2. Fallback to DescribeWorkflowExecution
	desc, err := s.temporalClient.DescribeWorkflowExecution(context.Background(), workflowID, "")
	if err != nil {
		http.Error(w, err.Error(), http.StatusNotFound)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"workflow_id": workflowID,
		"status":      desc.WorkflowExecutionInfo.Status.String(),
		"start_time":  desc.WorkflowExecutionInfo.StartTime.AsTime(),
		"close_time":  desc.WorkflowExecutionInfo.CloseTime.AsTime(),
	})
}

