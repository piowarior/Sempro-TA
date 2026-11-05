

## 2. Audit & Kritik Keras SRS / BRD Anda

Meskipun BRD/SRS Anda sudah direvisi dan jauh lebih baik dari draf awal, masih ada **3 celah kritis** yang bisa dijadikan peluru oleh Dosen Penguji Skeptis maupun *Senior Tech Lead*:

### 🔴 Celah 1: Masalah "Categorical Feature Leakage" pada MMD

* **Realita di BRD:** Anda menulis bahwa fitur kategorikal di-encode (*One-Hot/Target Encoding*) lalu di-scale sebelum dihitung jarak MMD-nya.
* **Kritik Keras:** Jika Anda menggunakan *Target Encoding*, Anda membutuhkan kolom target $Y$. Padahal, metrik *distribution distance* ($P(X)$) seharusnya dihitung secara *unsupervised* tanpa menyentuh $Y$. Jika MMD dihitung menggunakan fitur hasil *Target Encoding*, terjadi **Data Leakage Konseptual**!
* **Perbaikan:** Di SRS/BRD, kunci aturan bahwa untuk perhitungan MMD/Wasserstein, fitur kategorikal **wajib menggunakan One-Hot Encoding atau Ordinal Encoding murni**, TANPA *Target Encoding*.

### 🔴 Celah 2: Kurangnya Uji Signifikansi Statistik pada Korelasi Spearman ($r_s$)

* **Realita di BRD:** Anda mengevaluasi korelasi antara *distance* dan degradasi menggunakan Spearman Rank.
* **Kritik Keras:** Menghitung koefisien $r_s$ saja tidak cukup. Jika sampel titik *severity* Anda sedikit (misal hanya 3 titik: 0.25, 0.50, 1.00), nilai korelasi bisa sangat tinggi secara kebetulan (*spurious correlation*).
* **Perbaikan:** Tambahkan pengujian nilai-$p$ (*p-value significance test*, $\alpha = 0.05$) pada modul `reliability/correlation.py` untuk membuktikan apakah korelasi tersebut signifikan secara statistik atau hanya variasi acak.

### 🔴 Celah 3: Skenario Covariate Shift Masih Bertipe Sintetis Murni

* **Realita di BRD:** Skenario injeksi dibatasi pada *Mean Shift*, *Variance Shift*, dan *Outlier Ingestion*.
* **Kritik Keras:** Penguji berpengalaman akan bilang: *"Dunia nyata tidak menggeser rata-rata seluruh fitur secara serentak (Mean Shift global). Pergeseran dunia nyata biasanya terjadi pada subpopulasi tertentu (Subpopulation Shift)."*
* **Perbaikan:** Pertahankan 3 skenario tersebut untuk cakupan TA S1 Anda, tetapi berikan disclaimer tegas di Bab III/Constraint BRD bahwa ini adalah *synthetic controlled baseline testing*, bukan *real-world natural drift* (seperti TableShift).


# 2. Kritik keras terhadap output framework kamu



Tetapi output tersebut belum menjamin kualitas hasil.

| Output                   | Kritik saya                                                                                                                |
| ------------------------ | -------------------------------------------------------------------------------------------------------------------------- |
| Distance vs Degradation  | Grafik korelasi saja tidak cukup untuk membuktikan distance bisa menjadi indikator yang konsisten.                         |
| Executive Summary        | Harus merangkum konfigurasi, temuan aktual, breach, keterbatasan, dan status eksperimen. Jangan hanya ringkasan angka.     |
| Reliability Profile CSV  | Bagus untuk analisis, tetapi perlu identitas run, seed, dataset, model, skenario, severity, metrik, threshold, dan status. |
| Reliability Profile JSON | Jangan sampai isinya berbeda dengan CSV. Harus ada schema/version yang jelas.                                              |
| Severity vs Performance  | Harus menampilkan baseline dan variasi/repetisi bila ada, bukan sekadar satu garis tanpa konteks.                          |
| Threshold Breach Matrix  | Harus jelas breach berdasarkan metrik apa, aturan apa, dan ambang berapa.                                                  |

Ada satu masalah yang perlu kamu sadari.

Grafik adalah visualisasi. Reliability profile adalah hasil data. Executive summary adalah interpretasi.

Ketiganya tidak saling menggantikan.

Saya justru akan mempertahankan output tersebut, tetapi memperkuat isi dan validitasnya daripada memperbanyak format output.

# 3. Lubang paling serius dalam konsep penelitianmu

Ini bagian yang menurut saya paling penting. Saya urutkan berdasarkan risiko terhadap keabsahan penelitian.

Kritis 1

Apakah covariate shift yang kamu hasilkan benar-benar valid?

Ini potensi masalah metodologis terbesar.

Konsep dasarnya:

\\[ P\_{\text{train}}(X)\ne P\_{\text{test}}(X) \\]

sementara asumsi covariate shift mengharapkan:

\\[ P\_{\text{train}}(Y\mid X) =P\_{\text{test}}(Y\mid X) \\]

Kalau kamu mengubah nilai fitur secara sembarang tetapi mempertahankan label, kamu bisa mengubah hubungan fitur dan target juga.

Misalnya, data awal memuat hubungan tertentu antara pendapatan dan kelayakan kredit. Kemudian kamu menaikkan pendapatan semua orang, tetapi label kelayakan kredit tidak berubah. Data tersebut belum tentu merepresentasikan covariate shift yang valid.

Kritik: empat skenario belum tentu empat skenario yang metodologisnya valid.

Yang dibutuhkan:

- Definisi mekanisme pembentukan setiap skenario.
- Alasan mengapa mekanisme tersebut merepresentasikan perubahan \\(P(X)\\).
- Pemeriksaan dukungan data (support/positivity) dan kemungkinan perubahan hubungan target.
- Catatan keterbatasan, terutama jika shift dibentuk melalui seleksi atau pembobotan sampel.

Kritis 2

Severity kamu bisa jadi hanya angka buatan

Misalnya kamu menentukan:

- Low = parameter 0,2
- Medium = parameter 0,5
- High = parameter 0,8

Angka ini belum otomatis mempunyai arti ilmiah.

Parameter 0,8 pada location shift belum tentu setara dengan parameter 0,8 pada scale shift. Begitu pula kategori yang berubah proporsinya.

Jangan menyamakan:

- Configured severity: kekuatan intervensi yang kamu tetapkan.
- Realized shift: perubahan distribusi yang benar-benar dihasilkan.
- Performance impact: dampak terhadap kinerja model.

Ketiganya berbeda.

Kalau tidak dipisahkan, grafik severity vs performance dapat memberikan kesan bahwa eksperimen sudah terstandardisasi, padahal tingkat perubahan antarskenario belum tentu sebanding.

Kritis 3

Distance vs degradation bisa menghasilkan kesimpulan menyesatkan

Ini menyangkut inti penelitianmu.

Misalnya:

- Eksperimen A menghasilkan distance besar, tetapi penurunan F1 kecil.
- Eksperimen B menghasilkan distance lebih kecil, tetapi penurunan F1 besar.

Itu bukan otomatis kegagalan framework. Justru bisa menjadi temuan penting.

Namun, ada persoalan lebih dalam.

MMD dan Wasserstein mengukur perbedaan distribusi fitur secara keseluruhan berdasarkan representasi dan metrik yang digunakan. Model bisa sangat sensitif terhadap sebagian fitur saja.

Akibatnya, distribusi dapat berubah jauh tetapi prediksi tetap stabil. Atau distribusi berubah relatif kecil tetapi mengenai fitur yang sangat penting.

Maka:

- Korelasi positif tidak boleh diasumsikan sejak awal.
- Korelasi rendah bukan otomatis berarti metode distance salah.
- Korelasi keseluruhan dapat menyembunyikan perbedaan antar-skenario atau model.

Rekomendasi: laporkan analisis keseluruhan sekaligus per model dan per skenario. Spearman correlation dapat menjadi salah satu analisis, tetapi jangan menjadikannya satu-satunya bukti.

Kritis 4

Kamu belum bisa menjawab konsistensi dengan satu kali eksperimen

Dalam desain awal kita pernah membahas 2 dataset, 2 model, 4 skenario, 3 severity, dan 3 seed.

Jika semua kombinasi valid dan dijalankan:

\\[ 2\times2\times4\times3\times3=144 \\]

Ini 144 evaluasi kondisi bergeser, belum termasuk baseline dan kontrol lainnya.

Jumlah itu cukup masuk akal untuk rancangan awal, tetapi bukan jaminan statistik yang memadai.

Tiga seed bisa menjadi permulaan, bukan bukti otomatis bahwa variasi hasil sudah terwakili.

Kamu perlu menentukan:

- Seed digunakan untuk apa: split, sampling shift, atau keduanya?
- Baseline apa yang dibagikan antarpercobaan?
- Unit observasi untuk analisis korelasi apa?
- Bagaimana menghindari penghitungan berulang atas baseline yang sama seolah-olah observasi independen?
- Apakah hasil ditampilkan dengan rentang variasi atau ketidakpastian?

Ini penting karena 144 baris hasil bukan berarti 144 observasi independen.

Tinggi 5

Pemilihan dataset berpotensi menentukan seluruh kesimpulan

Dataset bukan sekadar file CSV yang bisa diunggah.

Kamu perlu mempertimbangkan:

- Apakah label tersedia dan cocok untuk klasifikasi?
- Apakah data memiliki fitur numerik dan kategorik?
- Apakah distribusi kelas mendukung evaluasi?
- Apakah jumlah data cukup setelah proses sampling/seleksi?
- Apakah terjadi data leakage?
- Apakah model dapat mempelajari hubungan yang cukup bermakna?

Dataset sintetis bisa membantu pengujian metodologi karena mekanisme pembentukan data lebih terkendali. Dataset nyata bisa membantu menunjukkan relevansi praktis, tetapi tidak otomatis membuktikan shift yang dibentuk realistis.

Jika sumber daya memungkinkan, pertimbangkan dataset sintetis untuk validasi mekanisme dan dataset nyata untuk evaluasi tambahan. Tidak harus banyak dataset.

Tinggi 6

Reliability threshold belum mempunyai dasar operasional yang cukup

Ambang reliabilitas tidak boleh hanya dipilih agar grafik threshold breach menarik.

Contoh kebijakan:

- F1-score minimum tertentu.
- Degradasi absolut maksimum tertentu.

Keduanya menjawab pertanyaan berbeda.

Misalnya, baseline F1 sudah rendah. Penurunan yang kecil belum tentu berarti model layak. Sebaliknya, model dengan baseline tinggi dapat mengalami degradasi tertentu tetapi tetap memenuhi kebutuhan aplikasi.

Kamu perlu membedakan:

- Ambang yang ditetapkan untuk eksperimen.
- Standar penerimaan dari konteks penggunaan nyata, jika tersedia.

Kalau tidak punya dasar domain, nyatakan sebagai ambang operasional eksperimen, bukan standar keandalan universal.

Status breach juga harus mendefinisikan kondisi tepatnya: apakah nilai sama dengan threshold masih lolos, metrik mana yang digunakan, dan bagaimana jika ada beberapa metrik.

## 4. Ada satu masalah konsep yang bisa mengubah desainmu

Saya cukup khawatir dengan penggunaan istilah stress testing.

Stress testing sering memberi kesan bahwa sistem diuji dalam kondisi ekstrem atau mendekati batas kemampuan.

Penelitianmu saat ini lebih spesifik:

Controlled distribution-shift stress testing untuk evaluasi ketahanan model.

Itu cocok, asalkan kamu memang melakukan pengujian dengan kondisi yang dirancang secara bertingkat dan terkontrol.

Tetapi jika kenyataannya hanya:

1. Mengubah sedikit data.
2. Hitung MMD.
3. Hitung F1.
4. Buat grafik.

Maka reviewer bisa bertanya:

> Apa bedanya framework ini dengan pipeline eksperimen biasa yang ditambah grafik dan ekspor laporan?

Ini pertanyaan yang sangat mungkin muncul.

Jawabanmu harus berada pada protokol pengujian yang sistematis, dapat diulang, dapat dikonfigurasi, memiliki definisi pengukuran dan kriteria evaluasi yang jelas, bukan pada jumlah output atau banyaknya modul.

Penelitian terdahulu seperti TableShift, WhyShift, dan Control+Shift juga perlu dibandingkan dengan cermat. Sintesis awal literatur kita sudah menunjukkan bahwa evaluasi distribusi shift, pembangkitan shift, dan analisis dampak performa telah memiliki penelitian pendahulu. Karena itu, integrasi komponen bukan novelty otomatis.&#x20;



# 5. Kritik terhadap struktur software yang kamu tunjukkan


Tetapi ada beberapa catatan.

| Bagian         | Kritik                                                                                                                            |
| -------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| `dataset/`     | Perlu validasi skema, target, tipe fitur, missing values, dan penanganan kelas minoritas.                                         |
| `shift/`       | Satu `generator.py` mungkin cukup untuk awal, tetapi skenario perlu kontrak dan metadata masing-masing.                           |
| `distance/`    | Pastikan preprocessing untuk model tidak otomatis menjadi preprocessing distance. Keduanya bisa membutuhkan representasi berbeda. |
| `models/`      | Pastikan pipeline fit tidak bocor dari data evaluasi.                                                                             |
| `reliability/` | Threshold, correlation, dan profile jangan mencampurkan perhitungan dengan interpretasi tanpa definisi jelas.                     |
| `tests/`       | Nama test menunjukkan cakupan yang baik, tetapi belum membuktikan kualitas test atau validitas ilmiah.                            |
| `output/`      | Harus bisa dilacak kembali ke konfigurasi dan versi eksperimen.                                                                   |
| `app.py`       | Perlu diperiksa apakah hanya UI/entry point atau justru menampung logika eksperimen terlalu banyak.                               |

### Hal yang menurut saya masih kurang dari arsitektur

Saya tidak menyarankan langsung menambah banyak direktori. Tetapi secara tanggung jawab, kamu perlu memastikan ada komponen atau mekanisme untuk hal berikut.

Experiment provenance / run registry

Setiap hasil harus bisa menjawab: run ini memakai dataset apa, model apa, konfigurasi apa, seed berapa, versi kode apa, dan kapan dijalankan?

Experiment validation

Validasi bahwa skenario berhasil dibuat, label tetap selaras, ukuran data sesuai, tidak ada kebocoran, dan asumsi eksperimen tidak dilanggar secara tidak sengaja.

Output schema dan versioning

CSV/JSON harus punya kontrak struktur data. Perubahan kolom atau definisi metrik harus bisa dilacak.

Run status dan error handling

Bedakan eksperimen sukses, gagal, dibatalkan, atau tidak valid. Jangan sampai kegagalan komputasi menghasilkan ringkasan seolah-olah hasil eksperimen sah.

Perhatikan juga folder `.git` yang sangat panjang pada struktur yang kamu kirim. Itu bukan bagian dari arsitektur aplikasi. Untuk dokumentasi, tampilkan struktur proyek dengan mengecualikan `.git`, `__pycache__`, dan `.pytest_cache`. Itu membuat dokumentasi lebih profesional dan fokus.

### misal saya buat machine learning gizi 

* **Skrip Python Anda (yang membersihkan `nilai-gizi.csv`, melatih Random Forest/XGBoost, lalu menyimpannya jadi `.pkl`):** Itu adalah proses **pabrik perakitan mobil dan uji jalan normal**. Anda menciptakan modelnya, melatihnya, dan memastikan model itu pintar di data yang normal.
* **Framework Stress Testing Anda (yang ada di struktur folder `Sempro-TA`):** Itu adalah **lintasan uji tabrak (*crash test track*)** lengkap dengan alat sensor pengukur kerusakannya.

Jadi, **modelnya tetap model buatan Anda**, tetapi model itu **dimasukkan ke dalam framework Anda untuk diuji ketahanannya**. Framework Anda **tidak** membuat model baru dari nol, melainkan **menggunakan (mengonsumsi)** model `.pkl` yang sudah Anda buat tadi.

---

### Bagaimana Alur Penghubungnya? (Bagaimana Framework Masuk ke Celah Penelitian Anda)

Supaya Anda tidak bingung bagaimana kodingan `nilai-gizi.csv` Anda nyambung dengan struktur folder `framework/` di GitHub Anda, lihat alur integrasinya berikut ini:

#### 1. Tahap Pertama (Di luar Framework / Persiapan Awal)

Anda menjalankan kode Jupyter Notebook atau skrip Python Anda yang tadi:

1. Baca `nilai-gizi.csv`.
2. Beri label (*Sehat, Kurang Sehat, Tidak Sehat*).
3. Latih model Random Forest / XGBoost pakai `X_train`.
4. Simpan modelnya ke file: `model_gizi_rf.pkl` (beserta *scaler* dan *encoder*-nya).
*(Tahap ini selesai, Anda sudah punya senjata/model siap uji).*

#### 2. Tahap Kedua (Masuk ke Framework Utama Anda: `Sempro-TA`)

Sekarang, Anda masuk ke skrip utama framework Anda (misalnya lewat `run_experiments.py` atau `app.py`). Di sinilah kebaruan TA Anda bekerja secara otomatis:

1. **Memuat Model & Data (`framework/models/`):**
Framework Anda akan membaca file `model_gizi_rf.pkl` yang tadi Anda buat, serta mengambil data uji (`X_test`) Anda.
2. **Injeksi Pergeseran (`framework/shift/`):**
Modul *shift generator* di framework Anda mengambil data uji (`X_test`) tersebut, lalu sengaja "merusaknya" secara terkontrol (misalnya: *Mean Shift* — semua kadar gula di data uji dinaikkan 20%, atau *Variance Shift*). Inilah yang disebut **Controlled Covariate Shift**.
3. **Ukur Jarak (`framework/distance/`):**
Sebelum data uji yang sudah dirusak itu masuk ke model, modul MMD dan Wasserstein menghitung: *"Berapa sih jarak perbedaan antara data uji asli dengan data uji yang sudah digeser ini?"* (Misal ketemu nilai MMD = 0.45).
4. **Uji Model & Hitung Degradasi (`framework/models/evaluator.py` & `framework/reliability/degradation.py`):**
Data uji yang sudah digeser tadi **dimasukkan ke dalam model `.pkl` Anda**. Model Anda pasti akan kebingungan dan akurasinya turun (misalnya F1-Score turun dari 90% jadi 60%). Framework Anda lalu menghitung persentase degradasi penurunannya.
5. **Cek Threshold & Buat Profil (`framework/reliability/`):**
Framework mengecek apakah penurunannya sudah melewati batas toleransi (*Threshold* $1.5 \times \text{Std Dev}$). Terakhir, framework otomatis mencetak laporan akhir berupa file yang ada di folder `output/`:
* `reliability_profile.csv` & `.json`
* `distance_vs_degradation.png`
* `severity_vs_performance.png`
* `executive_summary.md`



---

### Kesimpulan Penting untuk TA Anda

Jadi, riset Anda **bukan** berarti membuat model baru yang berbeda dari orang lain. Model *machine learning*-nya tetap standar (bisa pakai Random Forest atau XGBoost dari hasil latihan data gizi Anda).

**Nilai kebaruan (*novelty*) dan inti riset TA Anda terletak pada alur pengujian di atas:**

Bagaimana Anda membangun sebuah *framework* sistematis yang bisa **menguji mental model tersebut secara terukur, menghitung jarak pergeseran statistiknya (MMD/Wasserstein), mengevaluasi batas keamanannya (*reliability threshold*), dan menyajikannya dalam bentuk *Reliability Profile***.

Dengan kata lain: *Modelnya milik Anda (hasil skrip gizi tadi), tapi "mesin penguji ketahanannya" adalah Framework modular yang sedang Anda bangun di folder `Sempro-TA`.*