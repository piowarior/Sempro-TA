Saya sesuaikan dengan penelitianmu di Klinik Darus Syifa Holistik (DSH), bukan sekadar mengganti judul dan nama. Saya juga ubah isi setiap slide agar mengikuti penelitianmu yang menggunakan **Event-Driven Architecture (EDA) dan Durable Workflow Orchestration menggunakan Temporal**.

Karena PPT ini untuk tugas Kapita yang meminta rangkuman Bab I sampai Bab V, saya buat sebagai presentasi Tugas Akhir lengkap, bukan hanya Sempro. Ada satu pengamanan penting: **jangan mengarang hasil implementasi atau pengujian yang belum dilakukan**. Bagian tersebut harus menggunakan data aktual atau ditandai sebagai belum tersedia.

Berikut prompt yang sudah disesuaikan dan bisa langsung kamu gunakan.

# PROMPT PEMBUATAN PPT TUGAS AKHIR KAPITA

Saya ingin kamu membuat dan menjalankan skrip Python menggunakan library `python-pptx` untuk menghasilkan presentasi PowerPoint Tugas Akhir sebanyak **tepat 20 slide** berdasarkan file template `TEMPLATE PPT TUGAS AKHIR - FASILKOM.pptx` yang tersedia di direktori proyek ini.

Presentasi harus merangkum isi penelitian dari **Bab I sampai Bab V**, dengan fokus pada penelitian implementasi dan pengembangan perangkat lunak. Presentasi tidak boleh hanya berisi teori, tetapi harus menjelaskan permasalahan, metode penelitian, perancangan sistem, penerapan teknologi, implementasi aplikasi, pengujian, serta kesimpulan.

## A. ATURAN WAJIB PENGGUNAAN TEMPLATE

1. Jangan menimpa file asli `TEMPLATE PPT TUGAS AKHIR - FASILKOM.pptx`. Simpan hasil presentasi sebagai file baru bernama `Presentasi_TA_DSH_20_Slides.pptx`.
2. Sebelum membuat presentasi, buat dan jalankan skrip inspeksi untuk membaca seluruh struktur template, meliputi:
   - Jumlah slide dan seluruh `slide_layouts`.
   - Indeks dan jenis `shapes` pada setiap slide.
   - Isi `text_frame` dan paragraf.
   - Posisi setiap elemen (`left`, `top`, `width`, `height`).
   - Properti font bawaan, termasuk nama, ukuran, warna, dan ketebalan.
   - Elemen gambar, logo, header, footer, dan dekorasi.
3. Jangan mengubah background, logo, header, footer, warna tema, maupun elemen dekoratif bawaan template.
4. Gunakan 12 slide template asli sebagai dasar untuk membuat 20 slide. Duplikasi slide yang sesuai dengan mempertahankan struktur XML, relasi gambar, dan format aslinya secara aman.
5. Pertahankan properti font bawaan template semaksimal mungkin. Jika menambahkan teks, tabel, diagram, atau ilustrasi, gunakan warna dan gaya yang konsisten dengan template.
6. Jangan meletakkan teks atau objek pada area header dan footer. Seluruh konten tambahan harus berada di area konten.
7. Pastikan tidak ada teks yang terpotong, meluap, bertumpuk, atau keluar dari kotak. Gunakan kalimat singkat dan poin-poin yang mudah dibaca saat presentasi.
8. Ukuran font konten diusahakan minimal 14 pt. Jika ruang terbatas, atur ulang susunan konten atau bagi menjadi beberapa bagian tanpa mengurangi keterbacaan.
9. Jangan memenuhi slide dengan paragraf panjang. Gunakan tabel, diagram alur, ilustrasi arsitektur, dan poin-poin ringkas apabila sesuai.
10. Setelah selesai, lakukan pemeriksaan ulang terhadap seluruh 20 slide, termasuk jumlah slide, posisi elemen, keterbacaan teks, dan keberadaan gambar.
11. Jangan mengarang data, hasil pengujian, hasil implementasi, atau informasi klinik yang belum tersedia. Jika informasi belum tersedia, gunakan placeholder yang jelas dan jangan menyajikannya sebagai hasil penelitian.

## B. IDENTITAS PENELITIAN

**Judul Tugas Akhir:**

IMPLEMENTASI EVENT-DRIVEN ARCHITECTURE DENGAN DURABLE WORKFLOW ORCHESTRATION MENGGUNAKAN TEMPORAL PADA SISTEM MONITORING PERKEMBANGAN PASIEN

**Identitas Peneliti:**

- Nama: Muhamad Rohisul Iman
- NIM: 20230801270
- Program Studi: Teknik Informatika
- Fakultas: Fakultas Ilmu Komputer
- Universitas: Universitas Esa Unggul
- Dosen Pembimbing: Dr. Riya Widayanti, S.Kom., M.M.S.I.
- Objek Penelitian: Klinik Darus Syifa Holistik (DSH)
- Tahun: 2026-2027

## C. LANDASAN DAN KONTEKS PENELITIAN

Gunakan informasi berikut sebagai dasar penyusunan presentasi. Jangan mengubah fokus penelitian menjadi sistem rekam medis umum, aplikasi pencatatan aktivitas saja, atau sistem diagnosis otomatis.

### 1. Permasalahan Penelitian

Pengelolaan monitoring perkembangan pasien di Klinik DSH yang memiliki alur kerja panjang masih dilakukan secara manual menggunakan Excel, sehingga proses pemantauan sulit dikelola ketika pelaksanaannya tidak berjalan sesuai rencana.

Klinik memiliki dua kategori pasien:

- Pasien umum, yaitu pasien yang menjalani pemeriksaan sesuai kebutuhan kunjungan.
- Pasien MHLS (*Moslem Healthy Lifestyle*), yaitu peserta program pendampingan selama kurang lebih tiga bulan dengan 12 kali pertemuan.

Dalam program MHLS, pasien memperoleh pemeriksaan dan instruksi aktivitas mandiri untuk dijalankan selama periode tertentu. Evaluasi aktivitas mandiri saat ini dilakukan pada pertemuan berikutnya melalui kuis atau penilaian pasien.

Sistem yang diusulkan akan membantu klinik memantau laporan aktivitas mandiri secara berkala selama periode pelaksanaan, sehingga perkembangan aktivitas pasien dapat dipantau sebelum pertemuan berikutnya.

Jangan menyatakan bahwa klinik saat ini mengalami keterlambatan laporan harian, kehilangan data, atau gangguan sistem jika hal tersebut belum dibuktikan melalui pengumpulan data.

### 2. Research Gap

Penelitian sebelumnya telah membahas sistem monitoring pasien, komunikasi antarbagian sistem, dan pengelolaan alur kerja. Namun, berdasarkan penelitian yang telah ditelaah, belum ditemukan penelitian yang menggabungkan EDA dan Temporal untuk mengelola proses monitoring pasien dengan alur kerja panjang.

### 3. Novelty

Menggabungkan EDA dan Temporal dalam satu sistem untuk menghubungkan setiap tahapan monitoring serta menjaga agar proses tetap berjalan meskipun terjadi perubahan atau gangguan.

### 4. Tujuan Penelitian

Mengembangkan sistem monitoring perkembangan pasien berbasis web dan mobile dengan menerapkan EDA dan Temporal untuk membantu pengelolaan proses pemantauan yang memiliki alur kerja panjang dan tidak selalu berjalan sesuai rencana.

### 5. Teknologi dan Metode

- Event-Driven Architecture (EDA).
- Durable Workflow Orchestration menggunakan Temporal.
- Laravel sebagai backend.
- MySQL sebagai basis data.
- Aplikasi web untuk pihak klinik.
- Aplikasi mobile untuk pasien.
- Design Science Research (DSR) sebagai pendekatan penelitian.
- Incremental Development sebagai model pengembangan perangkat lunak.
- Black Box Testing dan System Usability Scale (SUS) untuk evaluasi sesuai pelaksanaan penelitian.

## D. PEMETAAN TEPAT 20 SLIDE

### SLIDE 1 - COVER

Gunakan Template Slide 1.

Isi:

- Judul lengkap Tugas Akhir.
- Nama mahasiswa.
- NIM.
- Program Studi Teknik Informatika.
- Fakultas Ilmu Komputer, Universitas Esa Unggul.
- Nama dosen pembimbing.
- Tahun penelitian.

Atur judul agar tetap terbaca dan tidak keluar dari area konten.

### SLIDE 2 - LATAR BELAKANG PENELITIAN

Gunakan Template Slide 2.

Isi:

- Pelayanan pasien melibatkan berbagai aktivitas dan tahapan yang saling berkaitan.
- Proses monitoring perkembangan pasien di Klinik DSH masih dikelola secara manual menggunakan Excel.
- Pasien MHLS menjalani program pendampingan selama tiga bulan dengan 12 kali pertemuan.
- Aktivitas mandiri pasien berlangsung di antara pertemuan klinik.
- Evaluasi aktivitas saat ini dilakukan pada pertemuan berikutnya.
- Dibutuhkan sistem yang dapat menghubungkan proses monitoring dan mengelola aktivitas yang berlangsung dalam periode tertentu.

Tampilkan hubungan antara permasalahan, kebutuhan sistem, dan teknologi yang digunakan.

### SLIDE 3 - IDENTIFIKASI MASALAH, RUMUSAN MASALAH, DAN TUJUAN

Gunakan duplikasi Template Slide 2.

Tampilkan secara ringkas:

**Identifikasi Masalah:**

1. Monitoring perkembangan pasien masih dicatat secara manual menggunakan Excel.
2. Proses monitoring pasien MHLS berlangsung dalam jangka waktu panjang dan melibatkan beberapa tahapan.
3. Pemantauan aktivitas mandiri perlu dilakukan selama periode pelaksanaan, bukan hanya saat pertemuan berikutnya.

**Rumusan Masalah:**

1. Bagaimana merancang dan membangun sistem monitoring perkembangan pasien berbasis web dan mobile sesuai kebutuhan Klinik DSH?
2. Bagaimana menerapkan EDA untuk menghubungkan dan mengintegrasikan proses antarbagian sistem melalui pertukaran event?
3. Bagaimana menerapkan Durable Workflow Orchestration menggunakan Temporal untuk mengelola alur monitoring pasien MHLS yang berlangsung dalam jangka waktu tertentu?
4. Bagaimana hasil pengujian fungsionalitas dan keandalan sistem dalam memproses event serta mempertahankan dan melanjutkan workflow setelah terjadi gangguan pada worker?

**Tujuan Penelitian:**\
Sesuaikan empat tujuan dengan empat rumusan masalah di atas. Gunakan kalimat yang singkat dan sejajar.

### SLIDE 4 - BATASAN DAN MANFAAT PENELITIAN

Gunakan Template Slide 7 atau 10 dengan tata letak dua kolom.

**Batasan Penelitian:**

- Objek penelitian adalah proses monitoring perkembangan pasien di Klinik DSH.
- Sistem terdiri atas aplikasi web untuk pihak klinik dan aplikasi mobile untuk pasien.
- Sistem memiliki lima modul utama: Pendaftaran Pasien, Kunjungan/Booking, Pemeriksaan, Monitoring Perkembangan Pasien, dan Aktivitas Mandiri.
- EDA digunakan untuk komunikasi antarbagian sistem melalui event.
- Temporal digunakan untuk pengelolaan workflow jangka panjang, khususnya aktivitas mandiri pasien MHLS.
- Tidak mencakup diagnosis otomatis, rekomendasi medis berbasis AI/ML, penggantian sistem rekam medis secara menyeluruh, integrasi perangkat eksternal, farmasi, dan keuangan.

**Manfaat Penelitian:**

- Teoretis: menjadi referensi penerapan EDA dan Temporal dalam sistem monitoring dengan workflow jangka panjang.
- Praktis: membantu pengelolaan data pasien, jadwal, pemeriksaan, monitoring perkembangan, dan aktivitas mandiri di Klinik DSH.
- Teknis: menghasilkan artefak perangkat lunak berbasis web dan mobile.

### SLIDE 5 - PENELITIAN TERDAHULU DAN RELEVANSINYA TERHADAP PENELITIAN

Gunakan Template Slide 3.

Tampilkan penelitian terdahulu yang menjadi dasar pemikiran penelitian ini. Jangan hanya mencantumkan nama peneliti dan tahun. Jelaskan secara ringkas fokus pembahasan setiap penelitian, temuan atau kontribusinya, serta relevansinya terhadap alasan pengembangan sistem monitoring pasien menggunakan EDA dan Temporal.

Gunakan delapan penelitian berikut:

1. **Soegaard Ballester et al. (2021)**
   Membahas penggunaan aplikasi yang terhubung dengan rekam medis elektronik untuk mendukung koordinasi alur kerja tim pelayanan kesehatan. Penelitian ini melaporkan penggunaan aplikasi pada 152 dari 169 layanan rawat inap di tiga rumah sakit. Relevansi: memperkuat alasan bahwa sistem digital dapat membantu mengintegrasikan informasi dan mendukung koordinasi tahapan pelayanan pasien.

2. **Stratil et al. (2026)**
   Membahas penggunaan aplikasi kesehatan digital Brisa untuk mendukung pencatatan gejala dan hasil penilaian kesehatan oleh pengguna. Penelitian melibatkan 4.194 pengguna, dengan sekitar 76% pengguna mencatat setidaknya satu gejala atau hasil penilaian melalui aplikasi. Relevansi: menunjukkan pemanfaatan aplikasi digital untuk mengumpulkan informasi perkembangan dan kondisi pasien.

3. **Nowell et al. (2023)**
   Membahas pemantauan pasien melalui aplikasi kesehatan digital dalam periode longitudinal. Penelitian melaporkan tingkat kepatuhan pengisian sebesar 57,2% untuk laporan harian dan 87,4% untuk laporan mingguan, serta membahas penggunaan pengingat untuk membantu pengisian laporan. Relevansi: memperkuat kebutuhan pengelolaan pelaporan dan pengingat aktivitas pasien secara berkala selama periode pemantauan.

4. **Syarif dan Pizaini (2022)**
   Membahas penerapan arsitektur *event-driven* pada sistem penerimaan peserta didik baru berbasis *microservices*. Penelitian ini menggunakan pertukaran *event* untuk menghubungkan proses antarkomponen sistem. Relevansi: menjadi dasar penggunaan EDA sebagai pendekatan komunikasi antarbagian sistem melalui *event*.

5. **Laigner et al. (2026)**
   Membahas tantangan dalam pengelolaan *event* pada sistem berbasis *microservices*, khususnya berkaitan dengan pengelolaan dan pemrosesan *event*. Relevansi: menunjukkan bahwa penerapan komunikasi berbasis *event* juga memerlukan pengelolaan proses dan hubungan antar-*event* yang baik.

6. **Bação dan Guerreiro (2026)**
   Membahas tantangan konsistensi dalam sistem *event-driven microservices*. Relevansi: memperkuat pertimbangan bahwa sistem yang menggunakan pertukaran *event* perlu memperhatikan konsistensi data dan proses antarbagian sistem.

7. **Nagpal (2026)**
   Membahas perbandingan teknologi *workflow orchestration*, termasuk Temporal, dalam pengelolaan proses yang berjalan dalam jangka waktu tertentu. Penelitian ini menjadi referensi untuk memahami kemampuan Temporal dalam mempertahankan status proses dan melanjutkan pekerjaan setelah terjadi gangguan. Relevansi: mendukung pemilihan Temporal untuk mengelola workflow monitoring pasien yang memiliki tahapan dan periode pelaksanaan.

8. **Yasini et al. (2025)**
   Membahas identifikasi dan pengelompokan *clinical events* dalam pelayanan kesehatan. Penelitian mengidentifikasi 168 jenis peristiwa klinis yang dikelompokkan ke dalam sembilan bidang pelayanan kesehatan. Relevansi: memperkuat dasar bahwa aktivitas pelayanan kesehatan dapat direpresentasikan sebagai peristiwa yang saling berkaitan, sehingga pendekatan berbasis *event* relevan untuk dipertimbangkan.

**Ketentuan penyajian slide:**

- Gunakan tabel dengan kolom: Peneliti dan Tahun, Fokus Penelitian, Kontribusi atau Temuan, dan Relevansi terhadap Penelitian Ini.
- Jangan menyalin seluruh uraian di atas ke dalam slide. Ringkas setiap penelitian menjadi satu sampai dua kalimat yang tetap menjelaskan substansinya.
- Kelompokkan pembahasan secara visual menjadi tiga fokus:
  1. **Sistem Digital dan Monitoring Pasien:** Soegaard Ballester et al., Stratil et al., dan Nowell et al.
  2. **Event-Driven Architecture:** Syarif dan Pizaini, Laigner et al., Bação dan Guerreiro, serta Yasini et al.
  3. **Durable Workflow Orchestration:** Nagpal.
- Pastikan setiap penelitian memiliki hubungan yang jelas dengan kebutuhan penelitian ini, bukan sekadar dicantumkan karena membahas teknologi yang serupa.
- Gunakan informasi sesuai isi sumber penelitian. Jangan mengarang metode, hasil, angka, atau kesimpulan yang tidak didukung oleh referensi.
- Cantumkan sitasi singkat pada setiap penelitian dan gunakan daftar pustaka yang sesuai.

### SLIDE 6 - RESEARCH GAP DAN NOVELTY

Gunakan duplikasi Template Slide 3.

Tampilkan:

- Penelitian terdahulu telah membahas monitoring pasien.
- Penelitian terdahulu telah membahas komunikasi antarkomponen menggunakan pendekatan event-driven.
- Penelitian terdahulu telah membahas pengelolaan workflow.
- Berdasarkan penelitian yang dikaji, belum ditemukan penggabungan EDA dan Temporal dalam sistem monitoring pasien dengan workflow jangka panjang.

**Novelty:**\
Integrasi EDA dan Temporal untuk menghubungkan tahapan monitoring pasien serta menjaga keberlanjutan workflow aktivitas mandiri MHLS.

Gunakan diagram sederhana yang memperlihatkan posisi penelitian dibandingkan penelitian terdahulu.

### SLIDE 7 - LANDASAN TEORI: SISTEM MONITORING DAN EVENT-DRIVEN ARCHITECTURE

Gunakan Template Slide 4.

Isi:

- Konsep sistem monitoring perkembangan pasien.
- Konsep event dalam sistem perangkat lunak.
- Event-Driven Architecture.
- Komponen utama EDA: Event Producer, Event Broker, dan Event Consumer.
- Mekanisme komunikasi: Producer menerbitkan event, Broker meneruskan event, Consumer memproses event sesuai kebutuhan.

Gunakan ilustrasi alur event sederhana. Jelaskan bahwa EDA digunakan untuk menghubungkan proses antarbagian sistem tanpa harus menggunakan pemanggilan langsung antarmodul.

### SLIDE 8 - LANDASAN TEORI: TEMPORAL DAN DURABLE WORKFLOW ORCHESTRATION

Gunakan duplikasi Template Slide 4.

Isi:

- Pengertian workflow jangka panjang.
- Durable Workflow Orchestration.
- Peran Temporal dalam mengelola workflow.
- Konsep Workflow, Activity, Worker, Timer, Signal, Retry, dan Persistence.
- Kemampuan mempertahankan status workflow dan melanjutkan proses setelah worker mengalami gangguan.

Gunakan diagram sederhana alur workflow:\
Workflow dimulai → Menunggu periode aktivitas → Menerima laporan atau event → Memperbarui status → Melanjutkan proses → Selesai sesuai kondisi program.

Jangan menyamakan Temporal dengan EDA. Jelaskan bahwa keduanya memiliki fungsi yang berbeda tetapi saling melengkapi.

### SLIDE 9 - LANDASAN TEORI: TEKNOLOGI DAN METODE PENGEMBANGAN

Gunakan duplikasi Template Slide 4.

Isi:

- Laravel dan MySQL untuk backend dan penyimpanan data.
- Aplikasi web untuk pihak klinik.
- Aplikasi mobile untuk pasien.
- Design Science Research (DSR).
- Incremental Development.
- Black Box Testing.
- System Usability Scale (SUS).

Tampilkan hubungan teknologi dan metode dalam bentuk diagram atau tabel ringkas.

### SLIDE 10 - METODE PENELITIAN

Gunakan Template Slide 5.

Isi:

**Pendekatan Penelitian:**\
Design Science Research (DSR).

**Model Pengembangan Perangkat Lunak:**\
Incremental Development.

**Tahapan DSR:**

1. Identifikasi Masalah dan Motivasi.
2. Penentuan Tujuan Solusi.
3. Desain dan Pengembangan.
4. Demonstrasi.
5. Evaluasi.
6. Komunikasi.

Tampilkan tahapan DSR sebagai alur proses yang jelas.

### SLIDE 11 - TAHAPAN PENELITIAN DAN PENGEMBANGAN SISTEM

Gunakan Template Slide 6.

Tampilkan:

- Identifikasi masalah.
- Studi literatur dan penentuan research gap.
- Analisis kebutuhan.
- Perancangan sistem.
- Pengembangan menggunakan Incremental Development.
- Demonstrasi dan pengujian.
- Evaluasi.
- Penyusunan laporan.

Tampilkan lima increment pengembangan:

1. Pendaftaran Pasien.
2. Kunjungan/Booking.
3. Pemeriksaan.
4. Monitoring Perkembangan Pasien.
5. Aktivitas Mandiri.

### SLIDE 12 - OBJEK PENELITIAN DAN PENGUMPULAN DATA

Gunakan Template Slide 7.

**Objek Penelitian:**\
Klinik Darus Syifa Holistik (DSH), Banten.

**Data Primer:**

- Observasi proses monitoring pasien.
- Wawancara semi-terstruktur dengan pemilik klinik.
- Klarifikasi proses operasional yang relevan.

**Data Sekunder:**

- Dokumen klinik yang diperoleh sesuai izin.
- Data dan format pencatatan pasien yang relevan.
- Penelitian terdahulu dan referensi ilmiah.

**Lingkungan Pengembangan:**

- Laravel.
- MySQL.
- Temporal.
- Aplikasi web.
- Aplikasi mobile.

Jangan mengarang alamat lengkap klinik atau data hasil wawancara yang belum tersedia.

### SLIDE 13 - ANALISIS PROSES BISNIS YANG SEDANG BERJALAN

Gunakan Template Slide 8.

Tampilkan diagram *swimlane* dengan dua aktor:

- Pasien.
- Pihak Klinik.

Alur:

1. Pasien mengajukan pendaftaran.
2. Klinik mencatat data dan menentukan jenis pasien.
3. Klinik menentukan jadwal kunjungan.
4. Pasien datang ke klinik.
5. Klinik melakukan pemeriksaan dan terapi.
6. Klinik mencatat hasil pemeriksaan dan terapi ke Excel.
7. Klinik memberikan instruksi aktivitas mandiri beserta periode pelaksanaan.
8. Pasien menerima instruksi dan pulang.
9. Jika pasien peserta MHLS, pasien menjalankan aktivitas mandiri selama periode yang ditentukan.
10. Pada pertemuan berikutnya, klinik melakukan pemeriksaan dan mengevaluasi aktivitas mandiri periode sebelumnya.
11. Proses berulang sampai 12 kali pertemuan.

Pasien umum tidak mengikuti siklus program MHLS.

### SLIDE 14 - ANALISIS PROSES BISNIS YANG DIUSULKAN

Gunakan duplikasi Template Slide 8.

Tampilkan diagram *swimlane* dengan tiga aktor:

- Pasien.
- Pihak Klinik.
- Sistem.

Alur utama:

1. Pasien mengajukan pendaftaran.
2. Klinik mencatat data, menentukan kategori pasien, dan menentukan jadwal pertama.
3. Sistem menyimpan data dan mengirimkan informasi jadwal.
4. Pasien datang untuk pemeriksaan dan terapi.
5. Klinik mencatat hasil pemeriksaan dan menentukan instruksi aktivitas mandiri.
6. Sistem menyimpan hasil pemeriksaan dan instruksi.
7. Jika pasien MHLS, klinik mengarahkan pasien untuk menggunakan aplikasi.
8. Pasien memasang aplikasi dan masuk menggunakan akun dari klinik.
9. Pasien mengajukan jadwal kunjungan berikutnya melalui aplikasi.
10. Klinik mengonfirmasi jadwal.
11. Pasien menjalankan aktivitas mandiri dan mengirimkan laporan secara berkala.
12. Sistem menyimpan laporan, memperbarui monitoring, dan mengirim pengingat sesuai jadwal.
13. Klinik memantau laporan aktivitas mandiri.
14. Pasien datang kembali untuk pemeriksaan dan evaluasi.
15. Sistem mengelola status pertemuan sampai program mencapai 12 kali pertemuan.

Pastikan proses pemasangan aplikasi terjadi setelah pasien ditetapkan sebagai peserta MHLS, bukan pada saat pendaftaran awal.

### SLIDE 15 - RANCANGAN ARSITEKTUR SISTEM

Gunakan Template Slide 9.

Tampilkan arsitektur sistem secara sederhana:

**Aplikasi:**

- Web Klinik.
- Mobile Pasien.

**Backend:**

- Laravel API.
- MySQL.

**Komunikasi:**

- Event Producer.
- Event Broker.
- Event Consumer.
- Event-Driven Architecture.

**Workflow:**

- Temporal Client.
- Temporal Server.
- Workflow.
- Activity.
- Worker.

Jelaskan hubungan antarkomponen:

- Aplikasi mengirim permintaan ke backend.
- Backend memproses data dan menerbitkan event.
- Event diterima oleh komponen yang membutuhkan.
- Temporal mengelola workflow aktivitas mandiri yang berlangsung dalam periode tertentu.
- Worker menjalankan aktivitas workflow dan dapat melanjutkan proses berdasarkan status yang tersimpan.

Jangan mengarang teknologi broker tertentu jika belum ditentukan dalam rancangan final.

### SLIDE 16 - RANCANGAN MODUL DAN BASIS DATA

Gunakan duplikasi Template Slide 9.

Tampilkan lima modul sistem:

1. **Pendaftaran Pasien:** pengelolaan data pasien dan kategori umum/MHLS.
2. **Kunjungan/Booking:** pengelolaan jadwal dan pengajuan kunjungan.
3. **Pemeriksaan:** pencatatan hasil pemeriksaan dan terapi.
4. **Monitoring Perkembangan Pasien:** penyajian riwayat pemeriksaan dan perkembangan pasien.
5. **Aktivitas Mandiri:** pengelolaan instruksi, periode, laporan pasien, pengingat, dan workflow Temporal.

Tampilkan enam kelompok data pemeriksaan:

- Gejala.
- Darah.
- Tubuh.
- Metabolik.
- Glukosa-Keton.
- Aktivitas Mandiri.

Bedakan lima modul sistem dengan enam kelompok data pemeriksaan. Keduanya bukan hal yang sama.

Tambahkan rancangan basis data atau ERD yang benar-benar sesuai dengan hasil perancangan. Jangan membuat nama tabel atau relasi seolah-olah sudah final jika belum dirancang.

### SLIDE 17 - RANCANGAN IMPLEMENTASI DAN PENGUJIAN

Gunakan Template Slide 8.

Tampilkan rencana implementasi:

- Aplikasi web untuk pihak klinik.
- Aplikasi mobile untuk pasien.
- Integrasi event antarbagian sistem.
- Pengelolaan workflow aktivitas mandiri dengan Temporal.

Tampilkan rencana pengujian:

**Black Box Testing:**

- Pendaftaran dan pengelolaan pasien.
- Pengelolaan jadwal.
- Pencatatan pemeriksaan.
- Monitoring perkembangan pasien.
- Pengelolaan instruksi aktivitas mandiri.
- Pengiriman laporan pasien.

**Pengujian EDA dan Temporal:**

- Pemrosesan event.
- Pengelolaan timer dan penantian workflow.
- Penerimaan signal atau laporan.
- Pengujian retry.
- Penghentian dan pemulihan worker.
- Pemeriksaan keberlanjutan status workflow.

**SUS:**\
Digunakan untuk mengukur usability berdasarkan hasil kuesioner pengguna jika evaluasi tersebut telah dilaksanakan.

Jika penelitian belum sampai tahap implementasi dan pengujian, tampilkan sebagai rancangan pengujian, bukan hasil pengujian.

### SLIDE 18 - HASIL IMPLEMENTASI DAN PEMBAHASAN

Gunakan Template Slide 10.

Bagian ini harus diisi berdasarkan hasil implementasi aktual.

Tampilkan screenshot aplikasi yang benar-benar telah dibuat, seperti:

- Dashboard pihak klinik.
- Halaman pendaftaran pasien.
- Halaman kunjungan/booking.
- Halaman pemeriksaan.
- Halaman monitoring perkembangan pasien.
- Halaman aktivitas mandiri pasien.
- Halaman laporan aktivitas mandiri.

Pilih screenshot yang paling penting agar tidak terlalu padat.

Jelaskan implementasi EDA dan Temporal berdasarkan kode dan hasil pengujian yang benar-benar tersedia.

Jika belum ada hasil implementasi, jangan membuat screenshot palsu atau mengklaim sistem sudah berjalan. Berikan placeholder yang jelas untuk diisi setelah implementasi selesai.

### SLIDE 19 - KESIMPULAN, SARAN, DAN DAFTAR PUSTAKA

Gunakan Template Slide 11.

Bagian atas:

**Kesimpulan:**\
Tuliskan kesimpulan berdasarkan hasil penelitian yang benar-benar telah dilakukan. Kesimpulan harus menjawab rumusan masalah dan tujuan penelitian.

**Saran:**\
Tuliskan pengembangan atau perbaikan yang relevan berdasarkan hasil evaluasi.

Bagian bawah atau bagian lanjutan yang tetap terbaca:

- Daftar pustaka utama yang digunakan dalam presentasi.
- Gunakan format sitasi APA 7.
- Jangan membuat referensi atau DOI yang tidak tersedia.
- Prioritaskan referensi yang benar-benar dikutip dalam slide.

Jika penelitian belum selesai, tandai bagian kesimpulan sebagai rancangan atau target penelitian, bukan hasil final.

### SLIDE 20 - TERIMA KASIH

Gunakan Template Slide 12.

Pertahankan desain asli template:

- TERIMA KASIH.
- Informasi kontak FASILKOM sesuai template asli.
- Jangan mengubah elemen dekoratif bawaan.

## E. ATURAN KONTEN DAN VISUALISASI

1. Gunakan bahasa Indonesia akademik yang sederhana, jelas, dan tidak bertele-tele.
2. Istilah teknis bahasa Inggris seperti *Event-Driven Architecture*, *Durable Workflow Orchestration*, *event*, *workflow*, *worker*, dan *monitoring* dapat dipertahankan sesuai konteks.
3. Gunakan huruf miring untuk istilah bahasa Inggris dalam kalimat bahasa Indonesia jika sesuai kaidah penulisan akademik.
4. Jangan menyalin seluruh paragraf skripsi ke dalam slide. Ringkas menjadi poin penting.
5. Prioritaskan diagram untuk menjelaskan:
   - Alur proses bisnis.
   - Arsitektur EDA dan Temporal.
   - Hubungan modul.
   - Tahapan penelitian.
   - Alur workflow aktivitas mandiri.
6. Gunakan tabel hanya jika membantu membandingkan informasi.
7. Jangan memasukkan terlalu banyak teks ke satu slide.
8. Gunakan screenshot aplikasi aktual, bukan ilustrasi aplikasi yang seolah-olah sudah diimplementasikan.
9. Setiap pernyataan ilmiah yang mengacu pada penelitian terdahulu harus memiliki sitasi singkat pada slide.
10. Jangan mengubah fakta proses bisnis Klinik DSH yang telah dijelaskan pada konteks penelitian.

## F. TAHAPAN PELAKSANAAN

Lakukan pekerjaan secara berurutan:

**Tahap 1 - Inspeksi Template**

- Periksa file template asli.
- Buat skrip inspeksi.
- Jalankan skrip dan tampilkan ringkasan struktur 12 slide.
- Identifikasi layout yang paling sesuai untuk setiap bagian presentasi.

**Tahap 2 - Pembuatan Skrip Generator**

- Buat skrip Python menggunakan `python-pptx`.
- Gunakan teknik duplikasi slide yang mempertahankan XML dan relasi gambar.
- Buat tepat 20 slide sesuai pemetaan.
- Pertahankan format visual template.

**Tahap 3 - Eksekusi**

- Jalankan skrip generator.
- Simpan sebagai `Presentasi_TA_DSH_20_Slides.pptx`.
- Pastikan file berhasil dibuat.

**Tahap 4 - Validasi**

- Pastikan jumlah slide tepat 20.
- Pastikan file dapat dibuka.
- Periksa seluruh teks dan objek.
- Pastikan tidak ada teks yang keluar dari kotak atau bertumpuk.
- Pastikan logo, header, footer, dan background template tetap utuh.
- Jika memungkinkan, render seluruh slide menjadi gambar dan periksa hasilnya satu per satu.

**Tahap 5 - Hasil Akhir**\
Berikan:

1. File PowerPoint hasil akhir.
2. Skrip inspeksi template.
3. Skrip generator PowerPoint.
4. Ringkasan hasil pemeriksaan 20 slide.
5. Daftar informasi yang masih perlu dilengkapi secara manual.

Mulai dengan **Tahap 1: inspeksi struktur file `TEMPLATE PPT TUGAS AKHIR - FASILKOM.pptx`**. Jangan langsung membuat presentasi sebelum struktur template diperiksa.

**Catatan penting:** Saya sengaja tidak memasukkan angka target seperti p95 3 detik, 20 pengguna bersamaan, atau 50 workflow aktif sebagai hasil penelitian. Itu hanya boleh ditampilkan jika memang sudah ditetapkan sebagai target pengujian dalam dokumen penelitianmu. Begitu juga screenshot aplikasi, hasil Black Box, dan skor SUS harus berasal dari implementasi sebenarnya.