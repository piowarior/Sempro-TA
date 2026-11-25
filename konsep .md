# PROMPT ANTIGRAVITY — PENGEMBANGAN SISTEM MONITORING PERKEMBANGAN PASIEN BERBASIS EDA DAN TEMPORAL

## 1. Identitas dan Tujuan Sistem

Kembangkan sebuah Sistem Monitoring Perkembangan Pasien untuk Klinik Darus Syifa Holistik (DSH) dengan judul penelitian:

**Implementasi Event-Driven Architecture dengan Durable Workflow Orchestration Menggunakan Temporal pada Sistem Monitoring Perkembangan Pasien**

Sistem dikembangkan dalam dua aplikasi yang saling terintegrasi:

1. **Web Admin Klinik:** digunakan pihak klinik untuk mengelola pendaftaran pasien, kunjungan, pemeriksaan, monitoring perkembangan, dan aktivitas mandiri.
2. **Mobile App Pasien:** digunakan pasien untuk melihat jadwal, hasil pemeriksaan, perkembangan kesehatan, instruksi aktivitas mandiri, pengingat, dan mengirimkan laporan aktivitas.

Tujuan sistem bukan sekadar menggantikan Excel dengan aplikasi pencatatan. Sistem harus membantu mengelola rangkaian proses monitoring pasien yang memiliki beberapa tahapan dan berlangsung dalam jangka waktu tertentu.

Fokus implementasi penelitian adalah:
- Event-Driven Architecture (EDA) untuk komunikasi antarbagian sistem melalui event.
- Durable Workflow Orchestration menggunakan Temporal untuk mengelola proses jangka panjang, termasuk timer, waktu tunggu, signal, retry, dan pemulihan workflow setelah gangguan.

## 2. Latar Belakang dan Permasalahan

Pengelolaan monitoring perkembangan pasien di Klinik DSH saat ini masih dilakukan secara manual menggunakan Excel. Prosesnya melibatkan beberapa tahapan, mulai dari pendaftaran pasien, penentuan jadwal kunjungan, pemeriksaan, pencatatan hasil, hingga pemberian instruksi aktivitas mandiri.

Terdapat dua kategori pasien:

1. **Pasien Umum:** menjalani pemeriksaan sesuai kebutuhan kunjungan. Pasien dapat menyelesaikan proses pelayanan setelah kunjungan tanpa harus mengikuti program monitoring berkala.
2. **Pasien MHLS (Moslem Healthy Lifestyle):** mengikuti program pendampingan selama tiga bulan dengan total 12 pertemuan. Selain pemeriksaan dan terapi di klinik, pasien memperoleh instruksi aktivitas mandiri yang dilaksanakan di luar klinik selama periode tertentu.

Pengelolaan tersebut memiliki tantangan karena data pemeriksaan tersimpan dalam pencatatan yang perlu dikelola kembali pada setiap pertemuan. Khusus pasien MHLS, proses monitoring juga melibatkan instruksi aktivitas mandiri dan laporan pelaksanaannya di antara jadwal pertemuan.

Pada proses yang berjalan, evaluasi aktivitas mandiri dilakukan ketika pasien datang kembali ke klinik melalui kuis mengenai aktivitas pada periode sebelumnya. Sistem yang diusulkan perlu mendukung pelaporan aktivitas secara berkala agar pihak klinik dapat memantau pelaksanaannya sebelum pertemuan berikutnya.

Dengan demikian, sistem perlu mengintegrasikan data pemeriksaan, riwayat perkembangan, jadwal kunjungan, instruksi aktivitas, dan laporan pasien. Sistem juga memerlukan pengelolaan workflow yang mampu menjalankan proses berdasarkan periode dan jadwal yang telah ditentukan.

## 3. Modul Utama Sistem

Sistem harus memiliki lima modul utama berikut. Jangan mengurangi atau menggabungkan modul-modul ini tanpa alasan yang jelas.

### Modul 1 — Pendaftaran Pasien

Digunakan pihak klinik untuk mengelola data pasien yang mendaftar.

Fitur:
- Menambahkan data pasien.
- Mengubah data pasien.
- Melihat detail pasien.
- Menentukan kategori pasien: Umum atau MHLS.
- Mengelola status pasien.
- Membuat akun pasien untuk akses aplikasi mobile.
- Mengelola riwayat data pasien.

Data pasien minimal:
- ID pasien.
- Nomor identitas pasien internal.
- Nama lengkap.
- Tanggal lahir.
- Jenis kelamin.
- Nomor telepon.
- Alamat.
- Kategori pasien.
- Status pasien.

Ketentuan:
- Pendaftaran awal dilakukan melalui pihak klinik.
- Pasien tidak membuat akun sendiri.
- Akun mobile diberikan oleh pihak klinik.
- Pasien umum tidak harus menggunakan aplikasi mobile apabila tidak mengikuti proses monitoring MHLS.

### Modul 2 — Kunjungan/Booking

Digunakan untuk mengelola jadwal kunjungan pasien ke klinik.

Fitur:
- Membuat jadwal kunjungan pertama.
- Melihat jadwal kunjungan.
- Mengelola riwayat kunjungan.
- Pasien MHLS mengajukan jadwal pertemuan berikutnya melalui aplikasi.
- Pihak klinik mengonfirmasi pengajuan jadwal.
- Sistem menyimpan jadwal yang telah dikonfirmasi.
- Sistem mengirimkan informasi jadwal kepada pasien.

Alur:
1. Pasien mengajukan pendaftaran.
2. Pihak klinik mencatat data dan menentukan kategori pasien.
3. Pihak klinik menentukan jadwal kunjungan pertama.
4. Sistem menyimpan dan mengirimkan informasi jadwal.
5. Pasien datang ke klinik sesuai jadwal.
6. Setelah pasien MHLS menggunakan aplikasi, pengajuan jadwal pertemuan berikutnya dapat dilakukan melalui aplikasi.
7. Pihak klinik mengonfirmasi jadwal yang diajukan.

Ketentuan:
- Jadwal kunjungan pertama ditentukan oleh pihak klinik.
- Pengajuan jadwal berikutnya melalui aplikasi berlaku untuk pasien MHLS.
- Sistem harus membedakan jadwal yang diajukan dan jadwal yang telah dikonfirmasi.
- Sistem mencatat nomor pertemuan MHLS hingga 12 kali.

### Modul 3 — Pemeriksaan

Digunakan pihak klinik untuk mencatat hasil pemeriksaan pasien pada setiap kunjungan.

Satu pasien dapat memiliki banyak riwayat pemeriksaan. Setiap pemeriksaan harus terhubung dengan pasien dan kunjungan yang bersangkutan.

Data pemeriksaan terdiri atas enam kelompok berikut.

#### 3.1 Gejala

Berisi 50 indikator pemeriksaan gejala atau indikator toksin.

Setiap indikator memiliki pilihan:
- Terindikasi.
- Tidak terindikasi.

Sistem menghitung jumlah indikator yang terindikasi dan menentukan level berdasarkan klasifikasi internal DSH:

| Jumlah indikator terindikasi | Level |
|---|---|
| 0–5 | Level 1 |
| 6–15 | Level 2 |
| 16 atau lebih | Level 3 |

Simpan seluruh jawaban, jumlah indikator terindikasi, level hasil, tanggal pemeriksaan, dan sesi pemeriksaan.

Klasifikasi tersebut merupakan ketentuan pencatatan internal DSH, bukan diagnosis medis otomatis.

#### 3.2 Darah

Berisi hasil pemeriksaan darah atau parameter terkait yang dicatat oleh pihak klinik.

Parameter yang perlu disediakan sesuai data pemeriksaan DSH:
- Tekanan darah sistolik.
- Tekanan darah diastolik.
- Heart rate.
- Hemoglobin.
- Kolesterol.
- Asam urat.

Setiap parameter harus memiliki satuan yang sesuai. Nilai pemeriksaan harus disimpan berdasarkan sesi pemeriksaan dan tanggalnya.

#### 3.3 Tubuh

Berisi hasil pengukuran kondisi tubuh pasien:

- Berat badan.
- IMT/BMI.
- Lingkar perut.
- Lemak tubuh.
- Kadar air.
- Massa otot.
- Rating fisik.
- Usia sel.
- Massa tulang.
- Lemak perut/visceral fat.
- Suhu tubuh.

Setiap parameter disimpan sebagai data terpisah agar dapat ditampilkan dalam riwayat dan grafik perkembangan.

#### 3.4 Metabolik

Sediakan bagian khusus untuk data pemeriksaan metabolik sesuai formulir dan dokumen pemeriksaan DSH.

Jangan mengarang nama parameter, satuan, nilai normal, kategori, maupun rumus yang belum tersedia dalam dokumen klinik. Jika informasi parameter belum lengkap, tandai sebagai kebutuhan konfirmasi dan jangan mengisi nilai asumsi.

#### 3.5 Glukosa-Keton

Berisi hasil pemeriksaan glukosa dan keton serta nilai GKI.

Data:
- Glukosa darah dalam mg/dL.
- Keton dalam mmol/L.
- Nilai GKI.

Gunakan rumus:

GKI = (Glukosa (mg/dL) / 18) / Keton (mmol/L)

Nilai GKI dihitung dari hasil glukosa dan keton yang tersedia. Simpan nilai pemeriksaan aslinya dan hasil perhitungan secara terpisah.

Jangan menganggap GKI sebagai hasil pemeriksaan mandiri yang tidak membutuhkan data glukosa dan keton.

#### 3.6 Aktivitas Mandiri

Berisi data evaluasi aktivitas mandiri pasien yang diperoleh dalam proses pemeriksaan atau evaluasi pertemuan.

Untuk pasien MHLS, data evaluasi aktivitas dari periode sebelumnya dapat dicatat berdasarkan kuis yang dilakukan ketika pasien datang kembali ke klinik.

Pisahkan data evaluasi tersebut dari laporan aktivitas berkala yang dikirimkan pasien melalui aplikasi mobile.

### Ketentuan umum pemeriksaan

- Tidak semua parameter wajib diperiksa pada setiap kunjungan.
- Nilai yang tidak diperiksa harus disimpan sebagai kosong/null, bukan nol.
- Riwayat pemeriksaan sebelumnya tidak boleh tertimpa ketika pemeriksaan baru dibuat.
- Hasil pemeriksaan harus dapat ditampilkan berdasarkan tanggal dan sesi pemeriksaan.
- Sistem tidak membuat diagnosis atau rekomendasi medis otomatis.

## Modul 4 — Monitoring Perkembangan Pasien

Modul ini mengintegrasikan hasil pemeriksaan dari setiap kunjungan untuk memperlihatkan perkembangan pasien dari waktu ke waktu.

Fitur web admin:
- Melihat ringkasan data pasien.
- Melihat riwayat seluruh pemeriksaan.
- Melihat perkembangan setiap kelompok pemeriksaan.
- Membandingkan hasil pemeriksaan antarperiode.
- Menampilkan grafik perubahan parameter.
- Melihat catatan perkembangan pasien.

Fitur mobile pasien:
- Melihat ringkasan perkembangan miliknya.
- Melihat tanggal pemeriksaan terakhir.
- Melihat hasil pemeriksaan yang diizinkan.
- Melihat riwayat pemeriksaan.
- Melihat grafik perkembangan berdasarkan parameter.
- Melihat ringkasan aktivitas mandiri yang sedang berjalan.

Ketentuan:
- Pasien hanya dapat melihat data miliknya sendiri.
- Pasien tidak dapat mengubah hasil pemeriksaan yang dicatat pihak klinik.
- Data yang tidak tersedia pada tanggal tertentu tidak boleh dianggap bernilai nol.
- Grafik hanya menampilkan parameter yang memiliki data pemeriksaan.
- Jangan membuat interpretasi atau diagnosis medis otomatis.

## Modul 5 — Aktivitas Mandiri

Modul ini digunakan untuk mengelola instruksi aktivitas mandiri pasien MHLS dan menjadi fokus utama penerapan Durable Workflow Orchestration menggunakan Temporal.

Pihak klinik memberikan instruksi aktivitas mandiri setelah pemeriksaan atau terapi. Pasien melaksanakan instruksi tersebut di luar klinik selama periode yang ditentukan.

Contoh aktivitas:
- Mengatur pola makan.
- Mengonsumsi makanan sesuai instruksi.
- Melakukan puasa sesuai arahan terapis.
- Melakukan aktivitas fisik.
- Mengatur waktu istirahat.
- Melakukan pengelolaan stres.

Contoh di atas bukan daftar tertutup. Pihak klinik harus dapat membuat instruksi sesuai kebutuhan pasien.

#### 5.1 Pengelolaan Instruksi

Pihak klinik dapat:
- Membuat instruksi aktivitas.
- Memilih pasien tujuan.
- Menentukan nama aktivitas.
- Menuliskan deskripsi instruksi.
- Menentukan periode mulai dan berakhir.
- Menentukan waktu pengingat.
- Mengubah instruksi yang masih berjalan.
- Membatalkan instruksi.

Jangan menambahkan kolom frekuensi sebagai ketentuan wajib apabila belum menjadi bagian dari aturan operasional DSH.

Setiap instruksi harus memiliki identitas sendiri dan terhubung dengan pasien.

#### 5.2 Tampilan Aktivitas Pasien

Pasien dapat melihat:
- Nama aktivitas.
- Deskripsi instruksi.
- Periode pelaksanaan.
- Waktu pengingat.
- Status aktivitas.
- Riwayat laporan.

Pasien tidak dapat mengubah instruksi yang diberikan pihak klinik.

#### 5.3 Pelaporan Aktivitas

Pasien dapat mengirimkan laporan pelaksanaan aktivitas melalui aplikasi mobile.

Data laporan minimal:
- ID laporan.
- ID pasien.
- ID instruksi aktivitas.
- Tanggal aktivitas.
- Waktu pengiriman laporan.
- Status pelaksanaan.
- Catatan tambahan jika tersedia.

Status laporan:
- Dilaksanakan.
- Tidak dilaksanakan.
- Terlambat, jika laporan diterima setelah batas waktu pelaporan pada hari yang bersangkutan.

Ketentuan pelaporan:
- Laporan merupakan pengakuan pasien, bukan bukti objektif bahwa aktivitas benar-benar dilakukan.
- Pasien hanya dapat mengirim laporan sesuai instruksi dan periode yang berlaku.
- Sistem harus membedakan aktivitas yang belum dilaporkan dengan aktivitas yang tidak dilaksanakan.
- Jika tanggal aktivitas sudah berganti, laporan untuk hari sebelumnya tidak boleh dianggap sebagai laporan tepat waktu.
- Jika perangkat sedang offline, laporan dapat disimpan sementara dan dikirimkan ketika koneksi tersedia kembali.
- Laporan yang sudah tersimpan harus dapat dilihat kembali oleh pasien dan pihak klinik.

#### 5.4 Pengingat Aktivitas

Sistem mengirimkan pengingat berdasarkan waktu yang ditentukan pihak klinik.

Pengingat harus tetap berjalan meskipun pasien tidak sedang membuka aplikasi.

Sediakan pencatatan riwayat pengingat dan status pengirimannya. Jangan mengklaim notifikasi eksternal berhasil apabila provider sebenarnya belum terintegrasi.

#### 5.5 Monitoring oleh Pihak Klinik

Pihak klinik dapat melihat:
- Instruksi aktivitas yang sedang berjalan.
- Periode setiap instruksi.
- Laporan aktivitas pasien.
- Aktivitas yang belum dilaporkan.
- Aktivitas yang dilaporkan terlambat.
- Aktivitas yang dilaporkan tidak dilaksanakan.
- Riwayat laporan.
- Status proses aktivitas.

Data laporan harus dapat digunakan pihak klinik untuk memantau pelaksanaan aktivitas sebelum pasien datang pada pertemuan berikutnya.

## 4. Alur Proses Sistem Secara Keseluruhan

Alur sistem harus mengikuti proses bisnis DSH berikut:

1. Pasien mengajukan pendaftaran.
2. Pihak klinik mencatat data pasien.
3. Pihak klinik menentukan kategori pasien, yaitu Umum atau MHLS.
4. Pihak klinik menentukan jadwal kunjungan pertama.
5. Sistem menyimpan data dan mengirimkan informasi jadwal.
6. Pasien datang ke klinik.
7. Pihak klinik melakukan pemeriksaan dan terapi.
8. Pihak klinik mencatat hasil pemeriksaan.
9. Pihak klinik menentukan instruksi aktivitas mandiri dan periodenya jika diperlukan.
10. Sistem menyimpan hasil pemeriksaan dan instruksi.
11. Pasien menerima instruksi dan pulang.

Untuk pasien umum, proses dapat berakhir setelah kunjungan sesuai kebutuhan pelayanan.

Untuk pasien MHLS:
1. Setelah kategori MHLS dipastikan dan instruksi awal diberikan, pihak klinik mengarahkan pasien untuk menggunakan aplikasi mobile.
2. Pasien menginstal aplikasi dan masuk menggunakan akun yang diberikan klinik.
3. Pasien melaksanakan aktivitas mandiri sesuai instruksi.
4. Pasien mengirimkan laporan aktivitas melalui aplikasi.
5. Sistem menyimpan laporan dan memperbarui informasi monitoring.
6. Pihak klinik memantau laporan aktivitas pasien.
7. Pasien mengajukan jadwal pertemuan berikutnya melalui aplikasi.
8. Pihak klinik mengonfirmasi jadwal.
9. Pasien datang untuk pemeriksaan dan terapi berikutnya.
10. Pihak klinik mengevaluasi aktivitas mandiri periode sebelumnya dan mencatat hasil pemeriksaan.
11. Pihak klinik memberikan instruksi aktivitas mandiri untuk periode berikutnya.
12. Proses berulang sampai program mencapai 12 pertemuan.

Sistem harus mencatat perkembangan setiap pertemuan dan status program MHLS. Penghitungan pertemuan tidak boleh hanya berdasarkan jumlah instruksi atau laporan aktivitas.

## 5. Penerapan Event-Driven Architecture

EDA digunakan untuk menghubungkan proses antarbagian sistem melalui pertukaran event.

Jangan memaksakan seluruh operasi CRUD menjadi event-driven. Gunakan event pada kejadian yang memang membutuhkan komunikasi atau pemicu proses lain.

Komponen yang perlu disiapkan:
1. Event Producer.
2. Event.
3. Event Dispatcher.
4. Event Handler/Consumer.
5. Transactional Outbox.
6. Idempotency.
7. Logging dan monitoring event.

Contoh event:
- `PatientRegistered`
- `VisitScheduled`
- `VisitConfirmed`
- `ExaminationRecorded`
- `PatientProgressUpdated`
- `ActivityPlanCreated`
- `ActivityPlanUpdated`
- `ActivityPlanCancelled`
- `PatientActivityReported`
- `ActivityReminderDue`
- `MHLSMeetingCompleted`

Gunakan Transactional Outbox Pattern agar penyimpanan data utama dan pencatatan event dapat dilakukan secara konsisten.

Setiap event minimal memiliki:
- `event_id`
- `event_type`
- `aggregate_id`
- `occurred_at`
- `payload`
- `correlation_id`
- `event_version`

Alur contoh:
1. Pihak klinik menyimpan instruksi aktivitas.
2. Backend melakukan validasi.
3. Backend menyimpan instruksi ke database.
4. Backend menyimpan event ke tabel outbox dalam transaksi yang sama.
5. Event dispatcher mengambil event yang belum diproses.
6. Event diteruskan ke handler.
7. Handler menjalankan proses yang relevan, misalnya memulai workflow Temporal.
8. Sistem mencatat status pemrosesan event.

Pastikan event yang diterima lebih dari satu kali tidak menyebabkan proses atau workflow ganda.

## 6. Penerapan Durable Workflow Orchestration Menggunakan Temporal

Temporal digunakan untuk mengelola workflow aktivitas mandiri pasien MHLS yang memiliki periode, jadwal pengingat, waktu tunggu, dan pelaporan.

EDA dan Temporal memiliki tanggung jawab berbeda:

- **EDA:** menyampaikan informasi mengenai kejadian yang berlangsung di dalam sistem.
- **Temporal:** mengelola urutan dan kelangsungan workflow berdasarkan kejadian, waktu, dan kondisi proses.

Buat workflow untuk mengelola satu instruksi aktivitas mandiri beserta jadwalnya.

Komponen Temporal:
- Temporal Server.
- Temporal Client.
- Temporal Worker.
- Workflow.
- Activity.
- Durable Timer.
- Signal.
- Retry Policy.

Contoh workflow:
`ActivityPlanWorkflow`

Alur:
1. Instruksi aktivitas dibuat oleh pihak klinik.
2. Backend menyimpan instruksi dan menerbitkan event.
3. Event handler menerima event.
4. Temporal Client memulai workflow.
5. Workflow mengelola periode dan jadwal pengingat.
6. Workflow menunggu waktu pengingat menggunakan durable timer.
7. Ketika waktunya tiba, workflow menjalankan Activity untuk mengirimkan pengingat.
8. Workflow menunggu laporan pasien melalui Signal.
9. Backend memvalidasi dan menyimpan laporan ke database.
10. Backend mengirimkan Signal kepada workflow terkait.
11. Workflow memperbarui status proses dan melanjutkan jadwal berikutnya.
12. Workflow berakhir ketika periode instruksi selesai atau instruksi dibatalkan.

Ketentuan:
- Gunakan durable timer Temporal, bukan sekadar `setTimeout`, `sleep`, atau cron job sebagai pengelola seluruh siklus workflow.
- Gunakan Signal untuk menyampaikan laporan pasien dan perubahan yang relevan kepada workflow.
- Laporan pasien harus disimpan ke database terlebih dahulu sebelum informasi laporan dikirim ke Temporal.
- Perubahan instruksi harus membuat workflow menyesuaikan jadwal yang berlaku.
- Pembatalan instruksi harus menghentikan proses pengingat yang tidak lagi relevan.
- Worker yang berhenti dan dijalankan kembali harus dapat melanjutkan workflow berdasarkan riwayat eksekusinya.
- Terapkan retry untuk Activity yang sesuai.
- Rancang Activity eksternal secara idempotent.

Jangan menyamakan status laporan aktivitas, status instruksi, dan status internal workflow Temporal.

## 7. Rancangan Data dan Database

Gunakan database relasional MySQL dengan struktur yang konsisten.

Entitas utama yang perlu dipertimbangkan:

1. `users`: akun petugas dan pasien.
2. `patients`: identitas dan kategori pasien.
3. `visits`: data kunjungan dan pertemuan pasien.
4. `appointments`: jadwal dan pengajuan booking.
5. `examination_sessions`: sesi pemeriksaan pasien.
6. `symptom_assessments`: ringkasan pemeriksaan gejala.
7. `symptom_assessment_answers`: jawaban setiap indikator gejala.
8. `blood_measurements`: hasil pemeriksaan darah.
9. `body_measurements`: hasil pemeriksaan tubuh.
10. `metabolic_measurements`: hasil pemeriksaan metabolik sesuai data DSH.
11. `glucose_ketone_measurements`: hasil glukosa, keton, dan GKI.
12. `activity_evaluations`: hasil evaluasi aktivitas mandiri pada pertemuan.
13. `activity_plans`: instruksi aktivitas mandiri.
14. `activity_schedules`: jadwal pelaksanaan dan pengingat.
15. `patient_activity_reports`: laporan aktivitas pasien.
16. `notification_logs`: riwayat pengiriman pengingat.
17. `outbox_events`: event yang menunggu atau telah diproses.
18. `workflow_references`: referensi workflow Temporal.
19. `audit_logs`: riwayat perubahan data penting.

Nama dan pemisahan tabel dapat disesuaikan setelah ERD dibuat. Jangan memaksakan tabel yang tidak diperlukan.

Pastikan:
- Primary key dan foreign key jelas.
- Relasi pasien, kunjungan, pemeriksaan, instruksi, dan laporan konsisten.
- Riwayat pemeriksaan tidak tertimpa.
- Data kosong tidak disimpan sebagai nol.
- Riwayat perubahan instruksi dapat ditelusuri.
- Setiap workflow memiliki referensi ke instruksi yang dikelolanya.
- Data pasien tetap berada di database aplikasi, bukan di Temporal.
- Status aplikasi tidak disamakan dengan status internal Temporal.

Buat ERD sebelum implementasi database.

## 8. Rancangan Arsitektur Aplikasi

Gunakan arsitektur yang memisahkan tanggung jawab setiap komponen.

Komponen:
1. Web Admin Klinik.
2. Flutter Mobile App Pasien.
3. Laravel Backend API.
4. MySQL Database.
5. Transactional Outbox.
6. Event Dispatcher dan Handler.
7. Temporal Client.
8. Temporal Server.
9. Temporal Worker.
10. Notification Provider.

Pilihan teknologi:
- Backend: Laravel.
- Mobile: Flutter.
- Database: MySQL.
- Workflow Engine: Temporal.
- Worker: Go dengan Temporal SDK.
- Containerization: Docker dan Docker Compose.
- API Testing: Postman.

Laravel menangani autentikasi, validasi, API, database, dan penerbitan event. Temporal Worker menjalankan workflow dan Activity menggunakan Temporal SDK.

Jangan mengasumsikan Laravel harus menjalankan workflow Temporal secara langsung. Verifikasi dukungan SDK dan mekanisme integrasi yang digunakan. Jangan membuat library, endpoint, atau API fiktif.

## 9. Hak Akses Pengguna

Sediakan dua aktor utama.

**Pihak Klinik**
- Mengelola data pasien.
- Menentukan kategori pasien.
- Mengelola jadwal kunjungan.
- Mencatat hasil pemeriksaan.
- Melihat riwayat perkembangan pasien.
- Membuat instruksi aktivitas mandiri.
- Mengubah atau membatalkan instruksi.
- Memantau laporan aktivitas.
- Memantau status workflow.

**Pasien**
- Masuk menggunakan akun dari klinik.
- Melihat jadwal kunjungan.
- Mengajukan jadwal pertemuan MHLS berikutnya.
- Melihat hasil pemeriksaan miliknya.
- Melihat grafik perkembangan.
- Melihat instruksi aktivitas.
- Menerima pengingat.
- Mengirim laporan aktivitas.
- Melihat riwayat laporannya sendiri.

Pastikan otorisasi diterapkan di backend. Pasien tidak boleh mengakses data pasien lain.

## 10. Rancangan Antarmuka

Buat tampilan profesional, sederhana, konsisten, responsif, dan mudah digunakan.

### Web Admin
Halaman utama:
1. Login.
2. Dashboard.
3. Daftar pasien.
4. Tambah dan detail pasien.
5. Riwayat kunjungan.
6. Pengelolaan jadwal/booking.
7. Pencatatan pemeriksaan.
8. Form Gejala.
9. Form Darah.
10. Form Tubuh.
11. Form Metabolik.
12. Form Glukosa-Keton.
13. Form Evaluasi Aktivitas Mandiri.
14. Riwayat pemeriksaan.
15. Grafik perkembangan pasien.
16. Daftar instruksi aktivitas.
17. Form instruksi aktivitas.
18. Monitoring laporan aktivitas.
19. Riwayat pengingat.
20. Monitoring workflow.

### Mobile App Pasien
Halaman utama:
1. Login.
2. Dashboard.
3. Jadwal kunjungan.
4. Pengajuan booking MHLS.
5. Riwayat pemeriksaan.
6. Detail hasil pemeriksaan.
7. Grafik perkembangan.
8. Daftar instruksi aktivitas.
9. Detail instruksi.
10. Pengingat.
11. Form laporan aktivitas.
12. Riwayat laporan.

Jangan menampilkan fitur yang tidak relevan dengan kategori pasien. Pasien umum tidak perlu melihat fitur aktivitas mandiri MHLS apabila tidak mengikuti program tersebut.

## 11. Pengujian Sistem

Pengujian harus mencakup fungsionalitas sistem dan implementasi EDA serta Temporal.

### Pengujian Fungsional
- Pendaftaran pasien.
- Penentuan kategori pasien.
- Pembuatan dan konfirmasi jadwal.
- Pencatatan pemeriksaan.
- Perhitungan klasifikasi gejala.
- Perhitungan GKI.
- Penyimpanan dan penampilan riwayat pemeriksaan.
- Visualisasi perkembangan.
- Pembuatan instruksi aktivitas.
- Pengiriman laporan pasien.
- Monitoring laporan oleh pihak klinik.
- Penghitungan pertemuan MHLS.

### Pengujian EDA
- Event diterbitkan setelah transaksi database berhasil.
- Event tidak diterbitkan jika transaksi utama gagal.
- Event dapat diproses ulang ketika terjadi kegagalan.
- Event duplikat tidak memulai workflow ganda.
- Correlation ID dapat digunakan untuk menelusuri proses.
- Riwayat pemrosesan event dapat dilihat.

### Pengujian Temporal
- Workflow berhasil dimulai.
- Workflow menunggu menggunakan durable timer.
- Workflow mengirim pengingat sesuai jadwal.
- Workflow menerima laporan melalui Signal.
- Workflow menerima perubahan instruksi.
- Workflow menerima pembatalan.
- Workflow melanjutkan jadwal berikutnya.
- Workflow dapat dilanjutkan setelah Worker dihentikan dan dijalankan kembali.
- Activity yang gagal dapat dicoba kembali sesuai retry policy.

Jalankan pengujian dengan skenario nyata yang dapat didemonstrasikan. Jangan hanya menunjukkan tampilan UI atau mengklaim fitur berjalan tanpa pengujian.

## 12. Batasan Pengembangan

Jangan menambahkan:
- AI atau Machine Learning.
- Diagnosis medis otomatis.
- Prediksi kondisi pasien.
- Rekomendasi terapi otomatis.
- Integrasi alat pemeriksaan eksternal yang belum menjadi kebutuhan.
- Modul farmasi.
- Modul pembayaran dan keuangan.
- Penggantian penuh rekam medis elektronik klinik.
- Fitur lain yang tidak termasuk dalam lima modul utama.

Jangan menganggap laporan aktivitas pasien sebagai bukti objektif pelaksanaan aktivitas.

Jangan menganggap pasien tidak melakukan aktivitas hanya karena belum mengirim laporan.

Jangan menjadikan semua operasi CRUD sebagai event-driven.

Jangan menjadikan Temporal sebagai database utama pasien.

Jangan menyamakan EDA dan Temporal karena keduanya memiliki fungsi berbeda.

## 13. Tahapan Pengerjaan

Kerjakan secara bertahap menggunakan pendekatan Incremental Development.

**Tahap 1 — Analisis dan Perancangan**
- Periksa project yang sudah tersedia.
- Identifikasi teknologi dan struktur project.
- Analisis kebutuhan.
- Buat ERD.
- Buat diagram arsitektur.
- Buat rancangan event.
- Buat rancangan workflow Temporal.
- Tentukan struktur API.

**Tahap 2 — Modul Pendaftaran Pasien**
- Database pasien.
- Pengelolaan data pasien.
- Kategori pasien.
- Akun pasien.
- Dashboard dan API.

**Tahap 3 — Modul Kunjungan/Booking**
- Jadwal kunjungan pertama.
- Pengajuan jadwal berikutnya.
- Konfirmasi jadwal.
- Riwayat kunjungan.
- Penghitungan pertemuan MHLS.

**Tahap 4 — Modul Pemeriksaan**
- Sesi pemeriksaan.
- Gejala.
- Darah.
- Tubuh.
- Metabolik.
- Glukosa-Keton.
- Evaluasi Aktivitas Mandiri.
- Riwayat pemeriksaan.

**Tahap 5 — Modul Monitoring Perkembangan Pasien**
- Dashboard web dan mobile.
- Riwayat pemeriksaan.
- Grafik perkembangan.
- Ringkasan perkembangan pasien.

**Tahap 6 — Modul Aktivitas Mandiri**
- Pembuatan instruksi.
- Pengelolaan periode dan pengingat.
- Tampilan instruksi pasien.
- Pelaporan aktivitas.
- Monitoring laporan oleh klinik.
- Penanganan laporan offline.

**Tahap 7 — Integrasi EDA dan Temporal**
- Transactional Outbox.
- Event Dispatcher.
- Event Handler.
- Idempotency.
- Temporal Server.
- Temporal Worker.
- Workflow aktivitas.
- Durable Timer.
- Signal.
- Retry.
- Recovery.

**Tahap 8 — Pengujian dan Dokumentasi**
- Pengujian fungsional.
- Pengujian event.
- Pengujian workflow.
- Pengujian gangguan Worker.
- Dokumentasi API.
- Dokumentasi event.
- Dokumentasi workflow.
- Panduan instalasi dan penggunaan.

Setiap increment harus dapat diuji sebelum dilanjutkan ke increment berikutnya.

## 14. Instruksi Penting untuk Antigravity

Sebelum melakukan coding:
1. Baca dan pahami seluruh konsep sistem.
2. Periksa project dan teknologi yang sudah tersedia.
3. Jangan langsung membuat seluruh fitur sekaligus.
4. Buat rancangan database dan arsitektur terlebih dahulu.
5. Pastikan lima modul utama dan enam kelompok data pemeriksaan terakomodasi.
6. Pisahkan data pemeriksaan, data monitoring, evaluasi aktivitas, dan laporan aktivitas pasien.
7. Pastikan alur pasien umum dan MHLS dibedakan.
8. Pastikan EDA dan Temporal memiliki tanggung jawab yang jelas.
9. Jangan membuat asumsi terhadap data atau aturan klinik yang belum tersedia. Catat sebagai kebutuhan konfirmasi.
10. Implementasikan sistem secara bertahap dan pastikan setiap fitur benar-benar berjalan.

**Prioritas utama:** menghasilkan sistem monitoring perkembangan pasien yang sesuai dengan proses DSH, memiliki pencatatan pemeriksaan yang terstruktur, mampu menampilkan perkembangan pasien dari waktu ke waktu, serta menerapkan EDA dan Temporal secara nyata untuk mengelola komunikasi antarproses dan workflow aktivitas mandiri pasien MHLS.