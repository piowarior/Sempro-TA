# PROMPT ANTIGRAVITY — IMPLEMENTASI SISTEM MONITORING PERKEMBANGAN PASIEN BERBASIS EVENT-DRIVEN ARCHITECTURE DAN TEMPORAL

## 1. Identitas Proyek

Buat sebuah sistem informasi dengan judul penelitian:

**IMPLEMENTASI EVENT-DRIVEN ARCHITECTURE DENGAN DURABLE WORKFLOW ORCHESTRATION MENGGUNAKAN TEMPORAL PADA SISTEM MONITORING PERKEMBANGAN PASIEN**

Sistem ini ditujukan untuk mendukung proses pencatatan perkembangan pasien dan monitoring aktivitas mandiri pasien pada Klinik Darus Syifa Holistik (DSH).

Sistem dikembangkan sebagai aplikasi yang terdiri dari:

- Web Admin: digunakan oleh petugas klinik untuk mengelola data pasien, mencatat hasil pemeriksaan, memberikan instruksi aktivitas mandiri, dan memantau perkembangan pasien.
- Mobile App Pasien: digunakan pasien untuk melihat perkembangan, melihat instruksi aktivitas mandiri, menerima pengingat, dan melaporkan pelaksanaan aktivitas.

Fokus utama pengembangan bukan sekadar membuat aplikasi CRUD, melainkan mengimplementasikan arsitektur berbasis event dan workflow yang mampu mengelola proses jangka panjang, penjadwalan, waktu tunggu, pelaporan, perubahan instruksi, serta pemulihan proses ketika terjadi gangguan.

---

# 2. Latar Belakang Permasalahan

Sistem dengan alur kerja yang panjang melibatkan berbagai tahapan dan aktivitas yang tidak selalu berjalan sesuai rencana. Perubahan proses, kesalahan pengguna, maupun gangguan teknis dapat menyebabkan alur kerja terhenti, tertunda, atau tidak terlaksana sesuai urutan.

Dalam konteks monitoring perkembangan pasien, petugas perlu mencatat perkembangan pasien secara berkala dan memberikan instruksi aktivitas mandiri yang harus dilakukan pasien dalam periode tertentu.

Aktivitas tersebut dapat memiliki jadwal, frekuensi, dan durasi berbeda-beda. Pasien juga dapat terlambat melaporkan aktivitas, melewatkan jadwal, atau memperoleh perubahan instruksi dari petugas.

Apabila seluruh proses tersebut hanya dikelola menggunakan proses request-response biasa, pengembang perlu membuat sendiri mekanisme penjadwalan, pengingat, waktu tunggu, pengelolaan status, pengulangan proses, dan pemulihan ketika aplikasi mengalami gangguan.

Oleh karena itu, sistem perlu menggunakan pendekatan arsitektur dan teknologi yang dapat menangani komunikasi antarproses serta pengelolaan workflow jangka panjang.

# 3. Solusi Penelitian

Untuk mengurangi ketergantungan antarproses, alur kerja dapat menerapkan **Event-Driven Architecture (EDA)** dengan memisahkan aktivitas ke dalam proses-proses yang saling berkomunikasi melalui event.

Namun, karena rangkaian proses tersebut dapat berlangsung lama dan membutuhkan waktu tunggu, diperlukan **Durable Workflow Orchestration menggunakan Temporal** agar setiap tahapan tetap tercatat dan dapat dilanjutkan ketika terjadi gangguan.

Pembagian tanggung jawab:

### Event-Driven Architecture

EDA digunakan untuk menyampaikan informasi bahwa suatu kejadian telah terjadi.

Contohnya:

- Instruksi aktivitas mandiri berhasil dibuat.
- Hasil pemeriksaan pasien berhasil disimpan.
- Pasien mengirimkan laporan aktivitas.
- Instruksi aktivitas diperbarui.
- Instruksi aktivitas dibatalkan.

Komponen lain dapat menerima event tersebut dan menjalankan proses yang relevan tanpa harus memiliki ketergantungan langsung dengan proses pembuat event.

### Temporal

Temporal digunakan untuk mengelola jalannya workflow yang membutuhkan waktu dan beberapa tahapan.

Contohnya:

- Menunggu waktu pengingat aktivitas pasien.
- Mengirimkan pengingat sesuai jadwal.
- Menunggu laporan aktivitas dari pasien.
- Menangani laporan yang terlambat atau tidak masuk.
- Melanjutkan aktivitas pada jadwal berikutnya.
- Menangani perubahan dan pembatalan instruksi.
- Melanjutkan workflow setelah Worker atau aplikasi mengalami gangguan.

**Prinsip utama: EDA mengatur komunikasi antarproses melalui event, sedangkan Temporal mengatur kelangsungan proses workflow dari awal sampai selesai.**

---

# 4. Ruang Lingkup Pengembangan Tahap Pertama

Untuk saat ini, jangan mengembangkan seluruh sistem klinik.

Fokus hanya pada tiga modul berikut.

## Modul A — Pencatatan Perkembangan Pasien

Modul ini digunakan petugas klinik untuk mencatat perkembangan pasien berdasarkan sesi pemeriksaan.

### A.1 Pengelolaan Data Pasien

Data pasien minimal meliputi:

- ID pasien.
- Nomor identitas pasien internal.
- Nama pasien.
- Tanggal lahir.
- Jenis kelamin.
- Nomor telepon.
- Alamat.
- Jenis pasien: Umum atau MHLS.
- Status pasien.

Akun pasien dibuat oleh petugas. Tidak perlu fitur pendaftaran mandiri oleh pasien.

### A.2 Pencatatan Sesi Pemeriksaan

Petugas dapat membuat sesi pemeriksaan baru untuk pasien.

Satu pasien dapat memiliki banyak sesi pemeriksaan.

Setiap sesi pemeriksaan mempunyai:

- ID sesi pemeriksaan.
- ID pasien.
- Nomor sesi pemeriksaan.
- Tanggal pemeriksaan.
- Catatan petugas.
- Hasil pemeriksaan.
- Waktu pencatatan.

Nomor sesi pemeriksaan tidak harus sama dengan nomor kunjungan pasien.

### A.3 Pencatatan Pemeriksaan Toxin Assessment

Sediakan formulir pemeriksaan yang memiliki 50 indikator.

Setiap indikator dapat memiliki status:

- Terindikasi.
- Tidak terindikasi.

Sistem menghitung jumlah indikator yang terindikasi secara otomatis.

Gunakan klasifikasi internal DSH berikut:

| Jumlah indikator terindikasi | Level |
|---|---|
| 0–5 | Level 1 |
| 6–15 | Level 2 |
| 16 atau lebih | Level 3 |

Simpan:

- Jawaban seluruh indikator.
- Jumlah indikator terindikasi.
- Level hasil klasifikasi.
- Tanggal pemeriksaan.
- Sesi pemeriksaan.

Catatan: klasifikasi ini merupakan klasifikasi internal yang digunakan untuk pencatatan dan monitoring sesuai ketentuan klinik, bukan diagnosis medis otomatis.

### A.4 Pencatatan Pemeriksaan Body dan Ketosis

Sediakan formulir pemeriksaan dengan field yang bersifat opsional karena tidak semua parameter diperiksa pada setiap sesi.

Parameter tubuh:

- Berat badan.
- BMI.
- Lingkar pinggang.
- Body fat.
- Water.
- Muscle mass.
- Physical rating.
- Cell age.
- Bone mass.
- Visceral fat.
- Body temperature.

Parameter darah dan ketosis:

- Tekanan darah.
- Heart rate.
- Hemoglobin.
- Glukosa.
- Kolesterol.
- Asam urat.
- Ketone.

Semua hasil pemeriksaan disimpan berdasarkan pasien dan sesi pemeriksaan.

Pastikan nilai yang tidak diperiksa dapat dibiarkan kosong, bukan otomatis diisi nol.

### A.5 Riwayat Perkembangan Pasien

Petugas dapat melihat:

- Riwayat seluruh sesi pemeriksaan.
- Riwayat hasil Toxin Assessment.
- Riwayat pemeriksaan Body dan Ketosis.
- Perbandingan nilai pemeriksaan dari waktu ke waktu.
- Grafik perubahan parameter tubuh dan darah.
- Catatan perkembangan pasien.

Gunakan grafik yang sesuai untuk memperlihatkan perubahan hasil pemeriksaan berdasarkan tanggal.

Jangan membuat diagnosis atau kesimpulan medis otomatis.

---

# 5. Modul B — Monitoring Perkembangan Pasien

Modul ini digunakan pasien melalui aplikasi mobile.

Pasien hanya dapat melihat data miliknya sendiri.

### B.1 Dashboard Pasien

Dashboard menampilkan:

- Identitas pasien.
- Jenis pasien.
- Ringkasan perkembangan terakhir.
- Tanggal pemeriksaan terakhir.
- Ringkasan hasil pemeriksaan.
- Grafik perkembangan.
- Aktivitas mandiri yang sedang berjalan.
- Aktivitas yang perlu dilaporkan.

### B.2 Riwayat Pemeriksaan

Pasien dapat melihat riwayat pemeriksaannya sendiri, meliputi:

- Tanggal pemeriksaan.
- Hasil Toxin Assessment.
- Hasil Body dan Ketosis.
- Catatan perkembangan yang diizinkan untuk ditampilkan.
- Perubahan nilai pemeriksaan dari waktu ke waktu.

Pasien tidak boleh mengubah hasil pemeriksaan yang dicatat petugas.

### B.3 Visualisasi Perkembangan

Sediakan grafik berdasarkan parameter yang tersedia.

Contoh:

- Grafik berat badan.
- Grafik BMI.
- Grafik lingkar pinggang.
- Grafik glukosa.
- Grafik kolesterol.
- Grafik asam urat.
- Grafik ketone.

Apabila data suatu parameter tidak tersedia pada tanggal tertentu, jangan membuat nilai palsu atau menganggapnya nol.

---

# 6. Modul C — Pengelolaan Aktivitas Mandiri Pasien MHLS

Ini merupakan modul utama untuk penerapan EDA dan Temporal.

Pasien MHLS dapat memperoleh instruksi aktivitas mandiri dari petugas klinik setelah sesi konsultasi atau pemeriksaan.

Aktivitas mandiri dapat berupa:

- Tidur sebelum pukul 22.00.
- Mengatur pola makan dan mengonsumsi real food.
- Membatasi makanan olahan.
- Melakukan puasa sunnah sesuai arahan terapis.
- Melakukan aktivitas fisik.
- Melakukan pengelolaan stres.

Aktivitas tersebut merupakan contoh. Petugas harus dapat menambahkan instruksi aktivitas lainnya secara dinamis.

## C.1 Pembuatan Instruksi Aktivitas oleh Petugas

Petugas dapat membuat instruksi aktivitas mandiri untuk pasien.

Formulir minimal:

- Pasien tujuan.
- Nama aktivitas.
- Deskripsi instruksi.
- Tanggal mulai.
- Tanggal berakhir.
- Jadwal aktivitas.
- Frekuensi aktivitas.
- Waktu pengingat.
- Catatan tambahan.
- Status instruksi.

Contoh:

Pasien Budi mendapatkan instruksi:

- Aktivitas: Tidur sebelum pukul 22.00.
- Deskripsi: Pasien diminta menjaga waktu tidur sebelum pukul 22.00.
- Tanggal mulai: 10 November 2026.
- Tanggal berakhir: 12 November 2026.
- Frekuensi: Setiap hari.
- Waktu pengingat: 21.00.

Contoh lain:

Aktivitas puasa sunnah dapat dijadwalkan hanya pada hari Senin dan Kamis.

Sistem harus mendukung jadwal yang berbeda-beda. Jangan mengasumsikan semua aktivitas wajib dilakukan setiap hari.

## C.2 Tampilan Aktivitas pada Aplikasi Pasien

Pasien dapat melihat:

- Nama aktivitas.
- Deskripsi instruksi.
- Jadwal aktivitas.
- Frekuensi.
- Periode aktivitas.
- Waktu pengingat.
- Status aktivitas.
- Riwayat pelaporan.

Pasien tidak dapat mengubah instruksi yang diberikan petugas.

## C.3 Pengingat Aktivitas

Sistem mengirimkan pengingat berdasarkan waktu yang telah ditentukan.

Contoh:

Pasien mendapatkan pengingat tidur sebelum pukul 22.00 pada pukul 21.00.

Pengingat harus dikirim sesuai jadwal, bukan hanya ketika pasien membuka aplikasi.

Gunakan sistem notifikasi yang dapat diganti provider-nya. Untuk tahap awal, implementasikan provider notifikasi yang tersedia atau mock notification jika kredensial layanan eksternal belum disediakan.

Jangan mengklaim notifikasi push eksternal berhasil apabila belum terhubung dengan provider sebenarnya.

## C.4 Pelaporan Aktivitas oleh Pasien

Setelah melakukan aktivitas, pasien dapat mengirimkan laporan.

Contoh:

- Aktivitas: Tidur sebelum pukul 22.00.
- Tanggal pelaksanaan: 10 November 2026.
- Status: Dilaksanakan.
- Catatan: Tidur pukul 21.45.

Sediakan status pelaporan:

- Dilaksanakan.
- Tidak dilaksanakan.

Sediakan catatan opsional.

Laporan harus menyimpan:

- ID pasien.
- ID instruksi aktivitas.
- Tanggal aktivitas.
- Waktu laporan.
- Status pelaksanaan.
- Catatan pasien.

Laporan merupakan pengakuan atau pelaporan mandiri pasien, bukan bukti objektif bahwa aktivitas benar-benar dilakukan.

Pasien dapat mengirim laporan untuk jadwal yang sesuai dengan instruksi. Jangan mewajibkan laporan setiap hari jika frekuensi aktivitas tidak mengharuskannya.

## C.5 Monitoring Aktivitas oleh Petugas

Petugas dapat melihat:

- Aktivitas yang sedang berjalan.
- Aktivitas yang sudah selesai.
- Aktivitas yang belum dilaporkan.
- Aktivitas yang tidak dilaksanakan.
- Riwayat laporan pasien.
- Jadwal pengingat.
- Periode instruksi.

Petugas dapat memperbarui atau membatalkan instruksi aktivitas.

Setiap perubahan harus tercatat dalam riwayat.

---

# 7. PENERAPAN EVENT-DRIVEN ARCHITECTURE

Implementasikan EDA pada proses yang memang membutuhkan komunikasi berbasis kejadian.

Jangan memaksakan seluruh operasi CRUD menjadi event-driven.

## A. Komponen EDA

Implementasikan komponen berikut:

1. Event Producer.
2. Event.
3. Event Dispatcher atau Event Handler.
4. Event Consumer.
5. Event Store atau Outbox.
6. Mekanisme idempotency.
7. Logging dan monitoring event.

Gunakan Transactional Outbox Pattern untuk mengurangi risiko data berhasil disimpan tetapi event gagal diterbitkan.

## B. Contoh Event

Buat event dengan struktur yang konsisten.

Contoh event:

- `PatientProgressRecorded`
- `ActivityPlanCreated`
- `ActivityPlanUpdated`
- `ActivityPlanCancelled`
- `ActivityReminderDue`
- `PatientActivityReported`
- `ActivityPlanCompleted`

Setiap event minimal mempunyai:

- event_id.
- event_type.
- aggregate_id.
- occurred_at.
- payload.
- correlation_id.
- event_version.

Jangan mencampurkan event dengan status database. Event menyatakan sesuatu yang telah terjadi.

## C. Alur Penerbitan Event

Contoh ketika petugas membuat instruksi aktivitas:

1. Petugas mengisi formulir aktivitas.
2. Backend melakukan validasi.
3. Backend menyimpan instruksi aktivitas ke database.
4. Dalam transaksi database yang sama, backend menyimpan event ke tabel outbox.
5. Event Dispatcher membaca event yang belum diproses.
6. Event diteruskan ke handler.
7. Handler menjalankan tindakan yang diperlukan.
8. Status pemrosesan event dicatat.

Gunakan mekanisme retry yang aman dan idempotent agar event yang dikirim ulang tidak membuat workflow ganda.

## D. Alur Event yang Harus Diimplementasikan

### Alur 1 — Pembuatan Aktivitas

`ActivityPlanCreated`

Event ini diterbitkan ketika petugas membuat instruksi aktivitas baru.

Consumer akan meminta Temporal Client untuk memulai workflow aktivitas tersebut.

### Alur 2 — Perubahan Aktivitas

`ActivityPlanUpdated`

Event ini diterbitkan ketika petugas memperbarui instruksi atau jadwal aktivitas.

Workflow yang sedang berjalan harus dapat menerima perubahan melalui mekanisme Signal atau mekanisme pembaruan workflow yang sesuai.

Pastikan jadwal lama tidak tetap berjalan setelah perubahan diterapkan.

### Alur 3 — Pembatalan Aktivitas

`ActivityPlanCancelled`

Event ini diterbitkan ketika petugas membatalkan instruksi.

Workflow harus menerima pembatalan dan menghentikan jadwal pengingat yang tidak lagi relevan.

### Alur 4 — Pelaporan Pasien

`PatientActivityReported`

Event ini diterbitkan setelah pasien mengirimkan laporan aktivitas.

Backend menyimpan laporan terlebih dahulu, lalu mengirimkan informasi laporan ke workflow Temporal melalui Signal.

Jangan mengirim laporan langsung ke Temporal tanpa menyimpan data laporan ke database.

---

# 8. PENERAPAN DURABLE WORKFLOW ORCHESTRATION MENGGUNAKAN TEMPORAL

Temporal harus menjadi komponen utama untuk mengelola siklus hidup aktivitas mandiri pasien yang berlangsung dalam periode tertentu.

## A. Konsep Workflow

Buat satu workflow untuk satu instruksi aktivitas mandiri.

Contoh:

Pasien Budi mendapatkan satu instruksi aktivitas tidur sebelum pukul 22.00 selama tiga hari.

Sistem membuat satu workflow:

`ActivityPlanWorkflow`

Workflow tersebut mengelola seluruh jadwal aktivitas selama tiga hari.

Jangan membuat satu workflow baru untuk setiap pengingat.

## B. Komponen Temporal

Implementasikan:

### 1. Temporal Server

Berfungsi sebagai engine yang menyimpan dan mengelola riwayat eksekusi workflow.

Gunakan Temporal Server yang sudah tersedia, bukan membuat workflow engine sendiri.

Untuk development, jalankan menggunakan Docker.

### 2. Temporal Client

Digunakan backend untuk:

- Memulai workflow.
- Mengirim Signal.
- Meminta pembatalan.
- Mengirim pembaruan instruksi.

### 3. Temporal Worker

Worker menjalankan kode Workflow dan Activity.

Worker harus dapat dijalankan sebagai proses atau container terpisah dari Laravel.

### 4. Workflow

Workflow mengatur urutan proses aktivitas mandiri.

### 5. Activity

Activity menjalankan operasi eksternal yang tidak boleh dilakukan langsung di kode Workflow.

Contohnya:

- Mengirim notifikasi.
- Mengambil data instruksi aktivitas jika diperlukan.
- Memperbarui status proses.
- Menjalankan integrasi eksternal.

### 6. Timer

Timer digunakan untuk menunggu jadwal tertentu.

Gunakan durable timer Temporal, bukan sekadar `setTimeout`, sleep biasa di backend, atau cron job untuk mengelola seluruh siklus workflow.

### 7. Signal

Signal digunakan untuk mengirimkan informasi dari backend ke workflow yang sedang berjalan.

Contoh:

- Pasien telah melaporkan aktivitas.
- Petugas mengubah jadwal.
- Petugas membatalkan instruksi.

## C. Alur Workflow Utama

Implementasikan alur berikut.

### Tahap 1 — Workflow Dimulai

1. Petugas membuat instruksi aktivitas.
2. Backend menyimpan instruksi.
3. Event `ActivityPlanCreated` diterbitkan.
4. Event handler menerima event.
5. Temporal Client memulai `ActivityPlanWorkflow`.
6. Workflow menerima parameter instruksi aktivitas.

### Tahap 2 — Menunggu Jadwal Pengingat

1. Workflow membaca jadwal aktivitas.
2. Workflow menunggu waktu pengingat menggunakan durable timer.
3. Ketika waktu pengingat tercapai, workflow memanggil Activity pengiriman notifikasi.

### Tahap 3 — Mengirim Pengingat

1. Workflow memanggil Activity `SendActivityReminder`.
2. Activity mengambil informasi pasien dan instruksi.
3. Activity mengirim notifikasi melalui notification provider.
4. Hasil pengiriman dicatat.
5. Workflow melanjutkan proses.

### Tahap 4 — Menunggu Laporan Pasien

1. Setelah pengingat dikirim, workflow menunggu laporan pasien melalui Signal.
2. Pasien dapat mengirim laporan melalui aplikasi.
3. Backend memvalidasi laporan.
4. Backend menyimpan laporan ke database.
5. Backend mengirim Signal ke workflow terkait.
6. Workflow menerima informasi laporan.
7. Workflow mencatat bahwa jadwal tersebut sudah mendapatkan laporan.

### Tahap 5 — Menangani Laporan Terlambat atau Tidak Masuk

Jika sampai batas waktu yang ditentukan pasien belum memberikan laporan:

1. Workflow dapat menandai jadwal sebagai belum dilaporkan.
2. Workflow dapat mengirim pengingat lanjutan jika aturan mengizinkan.
3. Workflow dapat mencatat bahwa batas waktu pelaporan telah terlewati.
4. Workflow tetap dapat melanjutkan ke jadwal berikutnya.

Jangan otomatis menganggap pasien tidak melakukan aktivitas hanya karena tidak mengirim laporan.

Bedakan:

- Belum dilaporkan.
- Dilaksanakan.
- Tidak dilaksanakan.
- Batas pelaporan terlewati.

### Tahap 6 — Melanjutkan Jadwal Berikutnya

Jika instruksi memiliki beberapa jadwal:

1. Workflow menunggu jadwal berikutnya.
2. Workflow mengirim pengingat sesuai jadwal.
3. Workflow menunggu laporan.
4. Proses berulang sampai periode instruksi berakhir.

### Tahap 7 — Penyelesaian Workflow

Jika seluruh jadwal dalam periode instruksi telah selesai:

1. Workflow memperbarui status proses menjadi selesai.
2. Workflow mencatat waktu penyelesaian.
3. Workflow berhenti secara normal.

Status workflow dan status pelaporan aktivitas harus dibedakan.

## D. Penanganan Perubahan dan Pembatalan

Workflow harus mendukung:

- Perubahan tanggal mulai.
- Perubahan tanggal berakhir.
- Perubahan frekuensi.
- Perubahan waktu pengingat.
- Pembatalan instruksi.

Gunakan Signal atau mekanisme update workflow Temporal yang tepat.

Ketika terjadi perubahan, pastikan workflow tidak menjalankan jadwal lama yang sudah tidak berlaku.

Ketika terjadi pembatalan, hentikan proses pengingat berikutnya secara aman.

## E. Penanganan Gangguan

Implementasikan dan demonstrasikan skenario berikut:

1. Worker berhenti ketika workflow sedang menunggu timer.
2. Worker dijalankan kembali.
3. Workflow dapat dilanjutkan berdasarkan riwayat eksekusinya.
4. Laporan pasien masuk ketika workflow sedang menunggu.
5. Event diterima lebih dari satu kali.
6. Notifikasi mengalami kegagalan.
7. Instruksi aktivitas dibatalkan ketika workflow sedang berjalan.

Gunakan retry policy untuk Activity yang sesuai.

Pastikan Activity eksternal seperti pengiriman notifikasi dirancang idempotent untuk mengurangi risiko pengiriman ganda.

Jangan menjanjikan exactly-once delivery untuk notifikasi eksternal. Jelaskan batasan antara jaminan eksekusi workflow Temporal dan keberhasilan pengiriman oleh provider eksternal.

---

# 9. RANCANGAN DATABASE

Rancang database relasional dengan struktur yang terpisah dan konsisten.

Minimal sediakan tabel berikut:

### Tabel `users`

Menyimpan akun login petugas dan pasien.

### Tabel `patients`

Menyimpan identitas pasien.

### Tabel `patient_progress_sessions`

Menyimpan sesi pemeriksaan pasien.

### Tabel `toxin_assessment_results`

Menyimpan ringkasan hasil Toxin Assessment.

### Tabel `toxin_assessment_answers`

Menyimpan jawaban dari masing-masing indikator.

### Tabel `body_measurements`

Menyimpan hasil pemeriksaan parameter tubuh.

### Tabel `blood_measurements`

Menyimpan hasil pemeriksaan parameter darah dan ketosis.

### Tabel `activity_plans`

Menyimpan instruksi aktivitas mandiri.

### Tabel `activity_schedules`

Menyimpan jadwal aktivitas.

### Tabel `patient_activity_reports`

Menyimpan laporan aktivitas dari pasien.

### Tabel `notification_logs`

Menyimpan riwayat pengiriman notifikasi.

### Tabel `outbox_events`

Menyimpan event yang menunggu atau telah diproses.

### Tabel `workflow_references`

Menyimpan referensi workflow Temporal, seperti workflow_id, activity_plan_id, status sinkronisasi, dan waktu mulai.

Jika diperlukan, tambahkan tabel pendukung untuk audit log, master indikator, dan konfigurasi jadwal.

Pastikan:

- Primary key dan foreign key jelas.
- Relasi antartabel konsisten.
- Data historis tidak tertimpa ketika ada pemeriksaan baru.
- Instruksi yang diperbarui tetap memiliki riwayat perubahan.
- Workflow ID memiliki hubungan yang jelas dengan instruksi aktivitas.
- Status proses aplikasi tidak disamakan dengan status internal Temporal.

Buat ERD untuk seluruh tabel.

---

# 10. RANCANGAN ARSITEKTUR APLIKASI

Gunakan arsitektur yang sederhana, tetapi benar-benar memperlihatkan pemisahan tanggung jawab.

Komponen yang diharapkan:

1. Flutter Mobile App.
2. Web Admin.
3. Laravel Backend API.
4. MySQL Database.
5. Transactional Outbox.
6. Event Dispatcher / Event Handler.
7. Temporal Client.
8. Temporal Server.
9. Temporal Worker.
10. Notification Provider.

Jika belum ada project, gunakan rancangan teknologi berikut sebagai pilihan awal:

- Backend: PHP dan Laravel.
- Mobile: Dart dan Flutter.
- Database: MySQL.
- Workflow Engine: Temporal.
- Worker: Go dengan Temporal SDK.
- Containerization: Docker dan Docker Compose.
- API Testing: Postman.

Penting:

Jangan mengasumsikan Laravel harus menjalankan Temporal Workflow secara langsung.

Pisahkan backend Laravel dan Temporal Worker.

Laravel menangani request API, autentikasi, validasi, database, dan penerbitan event.

Worker Go menjalankan Workflow dan Activity menggunakan Temporal SDK.

Laravel berkomunikasi dengan Temporal melalui Temporal Client yang sesuai, misalnya melalui service atau adapter integrasi yang dapat dipelihara.

Sebelum memilih pendekatan integrasi, verifikasi dukungan SDK dan protokol Temporal yang digunakan. Jangan membuat library atau API fiktif.

Jika terdapat keterbatasan SDK pada stack yang dipilih, jelaskan dan pilih solusi integrasi yang benar-benar dapat dijalankan.

---

# 11. RANCANGAN ANTARMUKA

Buat UI yang profesional, sederhana, responsif, dan konsisten.

## A. Web Admin

Halaman minimal:

1. Login.
2. Dashboard.
3. Daftar pasien.
4. Detail pasien.
5. Riwayat pemeriksaan pasien.
6. Form pencatatan Toxin Assessment.
7. Form pencatatan Body dan Ketosis.
8. Grafik perkembangan pasien.
9. Daftar aktivitas mandiri pasien.
10. Form pembuatan instruksi aktivitas.
11. Detail aktivitas dan jadwal.
12. Riwayat pelaporan aktivitas.
13. Monitoring workflow aktivitas.
14. Riwayat notifikasi.

## B. Mobile Pasien

Halaman minimal:

1. Login.
2. Dashboard pasien.
3. Riwayat pemeriksaan.
4. Detail hasil pemeriksaan.
5. Grafik perkembangan.
6. Daftar aktivitas mandiri.
7. Detail instruksi aktivitas.
8. Pengingat aktivitas.
9. Form pelaporan aktivitas.
10. Riwayat aktivitas.

Buat desain yang mudah digunakan, terutama untuk pasien yang tidak terbiasa menggunakan aplikasi digital.

---

# 12. AUTENTIKASI DAN HAK AKSES

Implementasikan dua jenis pengguna utama:

### Admin/Petugas

Dapat:

- Mengelola pasien.
- Mencatat pemeriksaan.
- Melihat perkembangan seluruh pasien.
- Membuat instruksi aktivitas.
- Mengubah instruksi aktivitas.
- Membatalkan instruksi aktivitas.
- Memantau laporan pasien.
- Memantau status workflow.

### Pasien

Dapat:

- Melihat data miliknya.
- Melihat hasil pemeriksaan miliknya.
- Melihat grafik perkembangan miliknya.
- Melihat instruksi aktivitas.
- Menerima pengingat.
- Mengirim laporan aktivitas.
- Melihat riwayat laporan miliknya.

Pastikan otorisasi dilakukan di backend, bukan hanya menyembunyikan menu di frontend.

Pasien tidak boleh mengakses data pasien lain.

---

# 13. PENGUJIAN SISTEM

Siapkan pengujian fungsional dan pengujian khusus EDA serta Temporal.

## A. Pengujian Fungsional

- Login admin berhasil.
- Login pasien berhasil.
- Admin membuat data pasien.
- Admin mencatat pemeriksaan.
- Sistem menghitung hasil Toxin Assessment.
- Sistem menyimpan hasil Body dan Ketosis.
- Pasien melihat hasil pemeriksaan.
- Pasien melihat grafik perkembangan.
- Admin membuat instruksi aktivitas.
- Pasien melihat instruksi.
- Pasien mengirim laporan.
- Admin melihat laporan.

## B. Pengujian Event-Driven Architecture

- Event berhasil diterbitkan setelah transaksi database berhasil.
- Event tidak diterbitkan jika transaksi utama gagal.
- Event yang gagal diproses dapat dicoba kembali.
- Event duplikat tidak menghasilkan workflow ganda.
- Event memiliki correlation ID.
- Riwayat pemrosesan event dapat ditelusuri.

## C. Pengujian Temporal

- Workflow berhasil dimulai.
- Workflow menunggu timer.
- Workflow mengirim pengingat.
- Workflow menerima Signal laporan pasien.
- Workflow menangani laporan terlambat.
- Workflow melanjutkan jadwal berikutnya.
- Workflow menerima perubahan instruksi.
- Workflow menerima pembatalan.
- Workflow dapat melanjutkan setelah Worker dihentikan dan dijalankan kembali.
- Activity yang gagal dapat dicoba kembali sesuai kebijakan retry.

## D. Pengujian Skenario Gangguan

Buat demonstrasi yang jelas:

**Skenario 1: Worker mati ketika workflow menunggu pengingat.**

Hentikan Worker, tunggu beberapa saat, jalankan kembali Worker, lalu verifikasi workflow tetap dapat melanjutkan proses.

**Skenario 2: Pasien melaporkan aktivitas.**

Kirim laporan dari aplikasi pasien, pastikan laporan tersimpan di database, kemudian pastikan workflow menerima Signal.

**Skenario 3: Instruksi diubah.**

Ubah waktu pengingat melalui web admin, pastikan jadwal lama tidak dijalankan lagi.

**Skenario 4: Event duplikat.**

Kirim event yang sama lebih dari sekali, pastikan tidak ada workflow ganda.

**Skenario 5: Workflow dibatalkan.**

Batalkan instruksi aktivitas, pastikan pengingat berikutnya tidak dijalankan.

---

# 14. VISUALISASI DAN DOKUMENTASI TEKNIS

Selain membangun aplikasi, buat dokumentasi berikut:

1. Diagram arsitektur sistem.
2. Diagram komponen EDA.
3. Diagram alur event.
4. Diagram lifecycle workflow Temporal.
5. Activity Diagram proses pencatatan perkembangan pasien.
6. Sequence Diagram pembuatan instruksi aktivitas.
7. Sequence Diagram pengiriman pengingat.
8. Sequence Diagram pelaporan aktivitas pasien.
9. Sequence Diagram perubahan instruksi.
10. Sequence Diagram pemulihan workflow setelah Worker mengalami gangguan.
11. ERD database.
12. Dokumentasi API.
13. Dokumentasi event beserta payload.
14. Dokumentasi Workflow, Activity, Timer, dan Signal.
15. Panduan menjalankan seluruh komponen menggunakan Docker.

Gunakan PlantUML untuk diagram agar mudah diperbarui.

---

# 15. BATASAN DAN HAL YANG TIDAK BOLEH DILAKUKAN

1. Jangan menambahkan AI, Machine Learning, atau prediksi otomatis.
2. Jangan membuat diagnosis medis otomatis.
3. Jangan menganggap laporan pasien sebagai bukti objektif aktivitas.
4. Jangan menganggap pasien tidak melakukan aktivitas hanya karena tidak mengirim laporan.
5. Jangan mewajibkan seluruh aktivitas dilaporkan setiap hari.
6. Jangan membuat satu workflow baru untuk setiap pengingat.
7. Jangan menggunakan cron job sebagai pengganti keseluruhan workflow Temporal.
8. Jangan menggunakan timer biasa di backend sebagai pengganti durable timer Temporal.
9. Jangan menjadikan seluruh CRUD sebagai event-driven.
10. Jangan menganggap Temporal sebagai database utama data pasien.
11. Jangan menganggap EDA dan Temporal adalah teknologi yang sama.
12. Jangan mengirim laporan pasien ke Temporal sebelum laporan disimpan di database.
13. Jangan membuat notifikasi eksternal palsu seolah-olah berhasil dikirim.
14. Jangan menambahkan modul booking kunjungan, pembayaran, farmasi, rekam medis lengkap, atau modul klinik lainnya pada tahap pertama.

---

# 16. TAHAPAN PENGERJAAN

Kerjakan secara bertahap dan jangan langsung membuat semua komponen tanpa memeriksa konsistensi arsitektur.

### Tahap 1 — Analisis dan Perancangan

Sebelum coding:

- Periksa workspace dan project yang sudah tersedia.
- Identifikasi teknologi yang sudah digunakan.
- Buat struktur folder.
- Buat ERD.
- Buat diagram arsitektur.
- Buat diagram alur event.
- Buat rancangan workflow Temporal.
- Tentukan kontrak API dan struktur event.
- Jelaskan bagaimana Laravel, database, event handler, Temporal Server, dan Worker saling berkomunikasi.

### Tahap 2 — Implementasi Pencatatan Perkembangan

Bangun:

- Database pasien.
- Data sesi pemeriksaan.
- Toxin Assessment.
- Body dan Ketosis.
- Riwayat pemeriksaan.
- API pencatatan.
- Dashboard admin.

### Tahap 3 — Implementasi Monitoring Pasien

Bangun:

- Aplikasi mobile pasien.
- Autentikasi pasien.
- Dashboard perkembangan.
- Riwayat pemeriksaan.
- Grafik perkembangan.

### Tahap 4 — Implementasi Aktivitas Mandiri

Bangun:

- Pembuatan instruksi aktivitas.
- Pengaturan jadwal.
- Daftar aktivitas pasien.
- Pelaporan aktivitas.
- Monitoring aktivitas oleh admin.

### Tahap 5 — Integrasi EDA

Bangun:

- Event producer.
- Transactional outbox.
- Event dispatcher.
- Event handler.
- Idempotency.
- Event logging.
- Integrasi event dengan Temporal Client.

### Tahap 6 — Integrasi Temporal

Bangun:

- Temporal Server.
- Temporal Worker.
- ActivityPlanWorkflow.
- Activity pengiriman notifikasi.
- Durable Timer.
- Signal pelaporan pasien.
- Mekanisme perubahan instruksi.
- Mekanisme pembatalan.
- Retry dan recovery.

### Tahap 7 — Pengujian dan Dokumentasi

Lakukan pengujian fungsional, event, workflow, serta skenario gangguan.

Pastikan semua komponen benar-benar berjalan dan dapat didemonstrasikan.

---

# 17. HASIL AKHIR YANG DIHARAPKAN

Hasil akhir bukan sekadar mockup atau desain UI.

Saya membutuhkan aplikasi yang benar-benar dapat dijalankan dengan komponen berikut:

- Web Admin yang berfungsi.
- Mobile App Pasien yang berfungsi.
- Backend API.
- Database.
- Event Producer dan Consumer.
- Transactional Outbox.
- Temporal Server.
- Temporal Worker.
- Workflow aktivitas mandiri.
- Durable Timer.
- Signal.
- Notification integration.
- Logging dan monitoring.
- Docker Compose.
- Dokumentasi instalasi dan pengujian.

Buat juga satu skenario demonstrasi lengkap:

Petugas membuat instruksi aktivitas mandiri untuk seorang pasien MHLS selama tiga hari. Sistem menerbitkan event `ActivityPlanCreated`. Event handler memulai workflow Temporal. Workflow menunggu jadwal pengingat, mengirim notifikasi, menunggu laporan pasien, menerima laporan melalui Signal, kemudian melanjutkan ke jadwal berikutnya hingga periode instruksi selesai.

Demonstrasikan pula bahwa ketika Worker dihentikan dan dijalankan kembali, workflow tetap dapat dilanjutkan.

**Prioritas utama penelitian ini adalah membuktikan bahwa EDA dan Temporal benar-benar diterapkan dan memiliki fungsi yang jelas dalam proses pencatatan, monitoring, dan pengelolaan aktivitas mandiri pasien.**

Jangan hanya membuat aplikasi yang terlihat bagus. Pastikan arsitektur, event, workflow, timer, signal, retry, dan recovery benar-benar diimplementasikan serta dapat diuji.