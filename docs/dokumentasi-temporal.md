# DOKUMENTASI WORKFLOW ORCHESTRATION MENGGUNAKAN TEMPORAL (DSH-PENELITIAN)

## 1. Ikhtisar Arsitektur Workflow

Pada sistem monitoring perkembangan pasien Klinik Darus Syifa Holistik (DSH), **Temporal** bertindak sebagai engine *Durable Execution* untuk mengelola siklus instruksi aktivitas mandiri pasien (MHLS) yang berlangsung dalam periode waktu tertentu (misalnya 3 hari, 7 hari, atau bulanan).

Setiap instruksi aktivitas mandiri memiliki satu instans workflow:
- **Workflow Type**: `ActivityPlanWorkflow`
- **Workflow ID**: `activity-plan-{id}` (misal: `activity-plan-1`)
- **Task Queue**: `activity-plan-queue`

---

## 2. Definisi Workflow (`workflow.go`)

### A. Input Parameter (`ActivityPlanPayload`)
```go
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
```

### B. Status Internal (`WorkflowState`)
Workflow menyimpan state dinamis yang dapat di-*query* sewaktu-waktu:
- `PlanID`: ID instruksi aktivitas.
- `CurrentSchedule`: Objek jadwal tanggal yang sedang aktif.
- `Status`: `RUNNING`, `COMPLETED`, `CANCELLED`.
- `ProcessedDays`: Jumlah hari/jadwal yang telah dilalui.
- `LastReport`: Objek laporan terakhir dari pasien.
- `ReportsReceived`: Kumpulan seluruh laporan yang masuk.

---

## 3. Primitif Temporal yang Digunakan

### A. Durable Timer
- **Fungsi**: Menunggu jam pengingat tiba untuk tiap jadwal tanpa membebani database dengan loop polling atau sleep biasa di server.
- **Implementasi**:
  ```go
  timerFuture := workflow.NewTimer(ctx, timerDuration)
  ```
- **Karakteristik**:
  - Dikelola oleh Temporal Server.
  - Jika worker mati/restart saat timer berjalan, timer tetap berdetak di Temporal Server.
  - Saat waktu tercapai, server menjadwalkan task ke Task Queue untuk diambil worker yang aktif.

### B. Signal Channels (Komunikasi Asinkron Masuk)

1. **`patient-reported-signal`**
   - **Tujuan**: Menerima informasi bahwa pasien telah mengisi laporan mandiri melalui aplikasi mobile.
   - **Payload**: ID laporan, status pelaksanaan (`dilaksanakan` / `tidak_dilaksanakan`), dan catatan pasien.
   - **Mekanisme**: Diterima melalui selector saat workflow berada pada *window* menunggu laporan pasien.

2. **`update-plan-signal`**
   - **Tujuan**: Menerima perubahan jadwal atau jam pengingat dari petugas klinik.
   - **Mekanisme**: Membatalkan timer lama dan mengonfigurasi ulang urutan jadwal yang tersisa.

3. **`cancel-plan-signal`**
   - **Tujuan**: Menghentikan seluruh rangkaian workflow jika instruksi dibatalkan oleh klinik.
   - **Mekanisme**: Mengubah status menjadi `CANCELLED` dan keluar dari siklus workflow secara aman (*graceful exit*).

### C. Activities
Operasi non-deterministik dan interaksi dengan sistem eksternal dijalankan di dalam Activity:

1. **`SendActivityReminderActivity`**:
   - Menghubungi backend API (`POST /api/internal/schedules/{id}/reminder-sent`).
   - Mencatat pengiriman di tabel `notification_logs`.
   - Mengirimkan pengingat ke notifikasi/gateway mock.
   - Dilengkapi dengan *Temporal Retry Policy* (backoff coefficient 2.0, max attempts 3).

2. **`CompletePlanActivity`**:
   - Menghubungi backend API (`POST /api/internal/plans/{id}/complete`) saat seluruh jadwal selesai.
   - Menandai instruksi aktivitas sebagai selesai di database.

### D. Workflow Query
- **Query Name**: `get-workflow-state`
- Memungkinkan sistem backend atau dashboard mengecek status memori workflow secara langsung tanpa mengubah riwayat eksekusi.
