# PANDUAN EKSEKUSI & PENGUJIAN SISTEM (DSH-PENELITIAN)
## Implementasi Event-Driven Architecture dengan Durable Workflow Orchestration Menggunakan Temporal pada Sistem Monitoring Perkembangan Pasien

Dokumen ini memandu langkah demi langkah menjalankan sistem penelitian serta skenario demonstrasi untuk seminar proposal / skripsi.

---

## 1. Komponen Arsitektur yang Dijalankan

| Komponen | Port | Deskripsi |
| :--- | :--- | :--- |
| **Web Admin & API Backend** | `http://localhost:8000` | Laravel 11/12, Web Admin Klinik & REST API Pasien |
| **Temporal Server** | `localhost:7233` | Engine Workflow Orchestration (gRPC) |
| **Temporal Web UI** | `http://localhost:8088` | Dashboard visual history eksekusi workflow, timer, dan signal |
| **Temporal Worker & Bridge** | `http://localhost:9000` | Worker Go SDK & REST adapter penghubung Laravel ke Temporal |
| **Outbox Dispatcher** | Background Daemon | Worker yang membaca tabel `outbox_events` dan mendispatch ke Temporal |
| **MySQL 8.0 Database** | `localhost:3307` | Database relasional (dsh_db, temporal, temporal_visibility) |
| **Flutter Mobile App** | Mobile / Desktop | Aplikasi mobile pasien DSH |

---

## 2. Cara Menjalankan Seluruh Sistem (One-Command Start)

Buka terminal dan jalankan:

```bash
cd /home/piowarior/perkuliahan/DSH-Sempro/dsh-penelitian
docker compose up -d
```

Docker Compose akan secara otomatis:
1. Menjalankan MySQL 8.0 dan menyiapkan database `dsh_db`.
2. Menjalankan Temporal Server dan menginisialisasi schema Temporal secara otomatis.
3. Menjalankan Temporal Web UI di port 8088.
4. Menjalankan Temporal Worker (Go SDK) di port 9000.
5. Menjalankan Backend Laravel di port 8000, mengeksekusi migrasi tabel dan seed data awal.
6. Menjalankan Outbox Dispatcher sebagai daemon otomatis.

---

## 3. Akun Pengguna Bawaan (Seed Data)

### A. Petugas Klinik (Web Admin)
- URL: `http://localhost:8000/login`
- **Username**: `admin`
- **Password**: `password123`

### B. Pasien MHLS (Mobile App / Web)
- **Username**: `budi`
- **Password**: `pasien123`
- Profil: Budi Santoso (DSH-MHLS-001)

### C. Pasien Umum
- **Username**: `siti`
- **Password**: `pasien123`
- Profil: Siti Rahayu (DSH-UMUM-001)

---

## 4. Cara Menjalankan Mobile App Pasien (Flutter)

Buka terminal baru:

```bash
cd /home/piowarior/perkuliahan/DSH-Sempro/dsh-penelitian/mobile-app
flutter run
```

*Catatan*: Jika dijalankan di emulator Android, alamat API sudah disiapkan ke `10.0.2.2:8000`. Jika dijalankan di Chrome/Linux desktop, otomatis terhubung ke `http://127.0.0.1:8000/api`.

---

## 5. SKENARIO PENGUJIAN & DEMONSTRASI PENELITIAN

Berikut adalah skenario pengujian utama yang dapat Anda demonstrasikan kepada dosen pembimbing dan penguji:

### Skenario 1: Pencatatan Sesi Pemeriksaan & Toxin Assessment Otomatis
1. Buka Web Admin: `http://localhost:8000`.
2. Masuk sebagai petugas (`admin` / `password123`).
3. Buka menu **Data Pasien** &rarr; pilih pasien **Budi Santoso** &rarr; klik **Tambah Pemeriksaan**.
4. Isi data sesi, centang beberapa indikator toksin (misal 11 indikator).
5. Perhatikan: Sistem secara otomatis menghitung jumlah terindikasi dan mengubah badge klasifikasi menjadi **Level 2** secara interaktif.
6. Klik **Simpan Sesi Pemeriksaan**.
7. Verifikasi: Data tersimpan, event `PatientProgressRecorded` tercatat di tabel outbox, dan visualisasi grafik di tab **Grafik Perkembangan** otomatis terbarui.

---

### Skenario 2: Pembuatan Instruksi Aktivitas Mandiri & Pemicuan Workflow
1. Pada Web Admin, buka menu **Aktivitas Mandiri (MHLS)** &rarr; klik **Buat Instruksi Baru**.
2. Pilih pasien **Budi Santoso**, pilih template aktivitas: *"Tidur sebelum pukul 22.00"* selama 3 hari.
3. Klik **Buat Instruksi & Picu Temporal Workflow**.
4. Amati alur yang terjadi:
   - Backend menyimpan instruksi dan jadwal harian ke database.
   - Event `ActivityPlanCreated` dicatat ke `outbox_events` dalam transaksi yang sama.
   - Outbox Dispatcher membaca event, memanggil Temporal Client.
   - Temporal Server memulai `ActivityPlanWorkflow`.
5. Buka **Temporal Web UI** di `http://localhost:8088`.
   - Lihat workflow execution dengan ID `activity-plan-{id}`.
   - Klik workflow tersebut, lihat diagram alur dan event history: `TimerStarted` menandakan Durable Timer sedang aktif menunggu jadwal pengingat!

---

### Skenario 3: Pengingat Notifikasi & Laporan Pasien via Signal
1. Tunggu durable timer pengingat mencapai waktu yang ditentukan (atau simulasi delay).
2. Workflow mengeksekusi Activity `SendActivityReminderActivity`.
3. Notifikasi pengingat tercatat di menu **Outbox & Temporal** &rarr; tabel **Notification Delivery Logs**.
4. Buka Mobile App Pasien (login sebagai `budi` / `pasien123`).
5. Pada dashboard, jadwal aktivitas hari ini muncul.
6. Pasien menekan tombol **Lapor**, memilih status *"Dilaksanakan"*, menambahkan catatan *"Tidur pukul 21.45"*, lalu klik **Kirim Laporan**.
7. Amati alur:
   - Laporan tersimpan di database `patient_activity_reports`.
   - Event `PatientActivityReported` diterbitkan ke Outbox.
   - Outbox mengirimkan Signal `patient-reported-signal` ke Temporal Workflow.
   - Workflow menerima sinyal, mencatat status pelaporan hari tersebut, dan melanjutkan loop ke jadwal hari berikutnya!

---

### Skenario 4: Pengujian Ketahanan Gangguan (Worker Crash & Resume)
Ini adalah **bukti kunci** mengapa penelitian Anda menggunakan Temporal:
1. Saat workflow sedang berada dalam status menunggu timer (misalnya menunggu jadwal hari kedua):
   Buka terminal dan matikan Worker:
   ```bash
   docker stop dsh-temporal-worker
   ```
2. Buka Temporal UI `http://localhost:8088`: status workflow tetap aman dan tercatat di server, tidak ada state yang hilang.
3. Pasien tetap dapat beraktivitas.
4. Nyalakan kembali Worker:
   ```bash
   docker start dsh-temporal-worker
   ```
5. Perhatikan log Worker:
   ```bash
   docker logs -f dsh-temporal-worker
   ```
6. **Hasil**: Worker melakukan *event history replay* secara deterministik dan langsung melanjutkan workflow tepat di titik timer sebelumnya tanpa duplikasi eksekusi!
