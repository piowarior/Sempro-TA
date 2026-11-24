# KONTRAK DOMAIN EVENT — SISTEM MONITORING PERKEMBANGAN PASIEN DSH
## Berbasis Event-Driven Architecture (EDA) & Transactional Outbox

Setiap event disimpan ke dalam tabel `outbox_events` dalam transaksi database yang sama dengan entitas bisnis (Transactional Outbox Pattern), sehingga menjamin atomisitas (*no dual-write failure*).

---

### Struktur Standar Outbox Event

```json
{
  "event_id": "c9b74052-192a-4bc4-9d41-e94db6e6bc72",
  "event_type": "ActivityPlanCreated",
  "aggregate_type": "ActivityPlan",
  "aggregate_id": "1",
  "payload": {},
  "correlation_id": "7f09f2b3-5fa7-4f62-8959-1e34ff0b4685",
  "event_version": 1,
  "status": "pending | published | failed",
  "retry_count": 0,
  "last_error": null,
  "occurred_at": "2026-11-10T22:00:00Z",
  "published_at": null
}
```

---

### 1. `PatientProgressRecorded`
Diterbitkan saat petugas klinik selesai mencatat sesi evaluasi pemeriksaan fisik, Toxin Assessment 50 indikator, dan parameter darah/ketosis.

- **Aggregate Type**: `PatientProgressSession`
- **Aggregate ID**: `{session_id}`
- **Payload**:
```json
{
  "session_id": 1,
  "patient_id": 1,
  "session_number": "SESI-01-20261110",
  "session_date": "2026-11-10",
  "toxin_level": "Level 2",
  "total_toxin_indicated": 11,
  "weight": 84.5,
  "glucose": 145.0,
  "blood_pressure": "135/85",
  "recorded_at": "2026-11-10T09:30:00Z"
}
```

---

### 2. `ActivityPlanCreated`
Diterbitkan ketika petugas membuat instruksi aktivitas mandiri baru untuk pasien MHLS. Event Dispatcher membaca event ini dan memanggil Temporal Client untuk memulai `ActivityPlanWorkflow`.

- **Aggregate Type**: `ActivityPlan`
- **Aggregate ID**: `{plan_id}`
- **Payload**:
```json
{
  "plan_id": 1,
  "patient_id": 1,
  "patient_name": "Budi Santoso",
  "patient_phone": "081234567890",
  "name": "Tidur sebelum pukul 22.00",
  "description": "Pasien diminta menjaga waktu tidur sebelum pukul 22.00 untuk proses regenerasi.",
  "start_date": "2026-11-10",
  "end_date": "2026-11-12",
  "frequency_type": "daily",
  "schedule_days": ["all"],
  "reminder_time": "21:00",
  "schedules": [
    {"id": 1, "scheduled_date": "2026-11-10", "reminder_time": "21:00"},
    {"id": 2, "scheduled_date": "2026-11-11", "reminder_time": "21:00"},
    {"id": 3, "scheduled_date": "2026-11-12", "reminder_time": "21:00"}
  ]
}
```

---

### 3. `ActivityPlanUpdated`
Diterbitkan saat petugas mengubah jadwal atau isi instruksi aktivitas yang sedang berjalan. Event Dispatcher mengirimkan `update-plan-signal` ke workflow Temporal terkait.

- **Aggregate Type**: `ActivityPlan`
- **Aggregate ID**: `{plan_id}`
- **Payload**:
```json
{
  "plan_id": 1,
  "patient_id": 1,
  "name": "Tidur sebelum pukul 21.30",
  "description": "Jadwal dimajukan sesuai hasil evaluasi sesi 2.",
  "start_date": "2026-11-11",
  "end_date": "2026-11-13",
  "reminder_time": "20:30",
  "schedules": [
    {"id": 2, "scheduled_date": "2026-11-11", "reminder_time": "20:30", "status": "pending"},
    {"id": 4, "scheduled_date": "2026-11-13", "reminder_time": "20:30", "status": "pending"}
  ]
}
```

---

### 4. `ActivityPlanCancelled`
Diterbitkan saat instruksi dibatalkan. Event Dispatcher mengirimkan `cancel-plan-signal` ke workflow Temporal sehingga timer pengingat berikutnya dihentikan.

- **Aggregate Type**: `ActivityPlan`
- **Aggregate ID**: `{plan_id}`
- **Payload**:
```json
{
  "plan_id": 1,
  "patient_id": 1,
  "reason": "Dibatalkan karena kondisi fisik pasien memerlukan istirahat total",
  "cancelled_at": "2026-11-11T14:00:00Z"
}
```

---

### 5. `PatientActivityReported`
Diterbitkan setelah pasien mengisi laporan pelaksanaan aktivitas melalui aplikasi mobile. Laporan disimpan ke tabel `patient_activity_reports` terlebih dahulu, lalu outbox mengirimkan `patient-reported-signal` ke workflow Temporal.

- **Aggregate Type**: `PatientActivityReport`
- **Aggregate ID**: `{report_id}`
- **Payload**:
```json
{
  "report_id": 10,
  "plan_id": 1,
  "schedule_id": 1,
  "patient_id": 1,
  "activity_date": "2026-11-10",
  "execution_status": "dilaksanakan",
  "patient_notes": "Tidur pukul 21.45, badan terasa lebih segar",
  "report_time": "2026-11-10T22:05:00Z"
}
```
