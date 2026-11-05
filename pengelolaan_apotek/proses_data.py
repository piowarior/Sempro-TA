from pathlib import Path
import pandas as pd
import re

# ==========================================================
# KONFIGURASI
# ==========================================================

FOLDER = Path(__file__).resolve().parent

FILE_PENJUALAN = FOLDER / "sales_processed.csv"
FILE_STOK = FOLDER / "stock_processed.csv"

OUTPUT = FOLDER / "hasil_pengolahan"
OUTPUT.mkdir(exist_ok=True)

# ==========================================================
# FUNGSI BANTU
# ==========================================================

def baca_csv(path):
    if not path.exists():
        raise FileNotFoundError(f"File tidak ditemukan: {path}")

    # Semua kolom dibaca sebagai teks agar nomor nota dan kode obat
    # tidak berubah menjadi angka atau notasi ilmiah.
    try:
        return pd.read_csv(path, dtype=str, sep=None, engine="python")
    except UnicodeDecodeError:
        return pd.read_csv(
            path, dtype=str, sep=None, engine="python", encoding="latin1"
        )


def bersihkan_nama_kolom(df):
    df.columns = (
        df.columns.astype(str)
        .str.strip()
        .str.replace(" ", "_", regex=False)
    )
    return df


def ubah_nama_kolom(df, daftar_alias):
    """
    Mengubah nama kolom Inggris maupun variasi Bahasa Indonesia
    menjadi nama kolom standar.
    """
    hasil = {}

    for kolom in df.columns:
        nama = kolom.strip().lower()
        if nama in daftar_alias:
            hasil[kolom] = daftar_alias[nama]

    return df.rename(columns=hasil)


def pastikan_kolom(df, kolom_wajib, nama_file):
    kurang = [kolom for kolom in kolom_wajib if kolom not in df.columns]

    if kurang:
        raise ValueError(
            f"Kolom berikut tidak ditemukan pada {nama_file}: {kurang}\n"
            f"Kolom yang tersedia: {list(df.columns)}"
        )


def angka(nilai):
    """
    Mengubah nilai menjadi angka.
    Nilai kosong atau tidak valid menjadi 0.
    """
    teks = nilai.astype(str).str.strip()
    teks = teks.replace({"": "0", "nan": "0", "None": "0"})

    # Mendukung angka desimal dengan koma jika tidak ada titik.
    koma_desimal = teks.str.contains(",", regex=False) & ~teks.str.contains(
        ".", regex=False
    )
    teks.loc[koma_desimal] = teks.loc[koma_desimal].str.replace(
        ",", ".", regex=False
    )

    return pd.to_numeric(teks, errors="coerce").fillna(0)


def buat_tanggal(df):
    return pd.to_datetime(
        {
            "year": pd.to_numeric(df["Tahun"], errors="coerce"),
            "month": pd.to_numeric(df["Bulan"], errors="coerce"),
            "day": pd.to_numeric(df["Tanggal"], errors="coerce"),
        },
        errors="coerce",
    )


def klasifikasi_transaksi(jenis):
    """
    Klasifikasi tidak menghapus transaksi.
    Jenis yang belum dikenal ditandai untuk pemeriksaan.
    """
    teks = str(jenis).strip().lower()

    if teks.startswith("penjualan"):
        return "Penjualan"

    if teks.startswith("pembelian"):
        return "Pembelian"

    if "retur" in teks:
        return "Retur"

    if "mutasi" in teks:
        return "Mutasi"

    if "stokopname" in teks or "stock opname" in teks or "stockopname" in teks:
        return "Stok Opname"

    if "import data obat" in teks:
        return "Import Data Obat"

    return "Perlu Diperiksa"


# ==========================================================
# 1. BACA DATA
# ==========================================================

print("Membaca data sumber...")

penjualan = bersihkan_nama_kolom(baca_csv(FILE_PENJUALAN))
stok = bersihkan_nama_kolom(baca_csv(FILE_STOK))


# ==========================================================
# 2. STANDARISASI NAMA KOLOM PENJUALAN
# ==========================================================

alias_penjualan = {
    "year": "Tahun",
    "tahun": "Tahun",
    "month": "Bulan",
    "bulan": "Bulan",
    "day": "Tanggal",
    "tanggal": "Tanggal",
    "invoicenumber": "Nomor_Nota",
    "nomor_nota": "Nomor_Nota",
    "nomor_nota_penjualan": "Nomor_Nota",
    "drugcode": "Kode_Obat",
    "kode_obat": "Kode_Obat",
    "drugname": "Nama_Obat",
    "nama_obat": "Nama_Obat",
    "quantity": "Jumlah_Terjual",
    "jumlah_terjual": "Jumlah_Terjual",
    "unit": "Satuan",
    "satuan": "Satuan",
}

penjualan = ubah_nama_kolom(penjualan, alias_penjualan)

kolom_penjualan = [
    "Tahun",
    "Bulan",
    "Tanggal",
    "Nomor_Nota",
    "Kode_Obat",
    "Nama_Obat",
    "Jumlah_Terjual",
    "Satuan",
]

pastikan_kolom(penjualan, kolom_penjualan, "data penjualan")

penjualan = penjualan[kolom_penjualan].copy()

for kolom in ["Nomor_Nota", "Kode_Obat", "Nama_Obat", "Satuan"]:
    penjualan[kolom] = penjualan[kolom].fillna("").astype(str).str.strip()

penjualan["Jumlah_Terjual"] = angka(penjualan["Jumlah_Terjual"])
penjualan["Tanggal_Transaksi"] = buat_tanggal(penjualan)

penjualan["Masalah_Data"] = ""

penjualan.loc[
    penjualan["Tanggal_Transaksi"].isna(), "Masalah_Data"
] += "Tanggal tidak valid; "

penjualan.loc[
    penjualan["Kode_Obat"].eq(""), "Masalah_Data"
] += "Kode obat kosong; "

penjualan.loc[
    penjualan["Jumlah_Terjual"] < 0, "Masalah_Data"
] += "Jumlah terjual negatif; "

penjualan.loc[
    penjualan["Jumlah_Terjual"].eq(0), "Masalah_Data"
] += "Jumlah terjual nol; "


# ==========================================================
# 3. STANDARISASI NAMA KOLOM STOK
# ==========================================================

alias_stok = {
    "year": "Tahun",
    "tahun": "Tahun",
    "month": "Bulan",
    "bulan": "Bulan",
    "day": "Tanggal",
    "tanggal": "Tanggal",
    "time": "Waktu",
    "waktu": "Waktu",
    "documentnumber": "Nomor_Dokumen",
    "nomor_dokument": "Nomor_Dokumen",
    "nomor_dokumen": "Nomor_Dokumen",
    "transactiontype": "Jenis_Transaksi",
    "jenis_transaksi": "Jenis_Transaksi",
    "drugcode": "Kode_Obat",
    "kode_obat": "Kode_Obat",
    "drugname": "Nama_Obat",
    "nama_obat": "Nama_Obat",
    "initialstock": "Stok_Awal",
    "stok_awal": "Stok_Awal",
    "stockin": "Stok_Masuk",
    "stok_masuk": "Stok_Masuk",
    "stockout": "Stok_Keluar",
    "stok_keluar": "Stok_Keluar",
    "remainingstock": "Sisa_Stok",
    "sisa_stock": "Sisa_Stok",
    "sisa_stok": "Sisa_Stok",
    "unit": "Satuan",
    "satuan": "Satuan",
    "status": "Status",
}

stok = ubah_nama_kolom(stok, alias_stok)

kolom_stok_wajib = [
    "Tahun",
    "Bulan",
    "Tanggal",
    "Waktu",
    "Nomor_Dokumen",
    "Jenis_Transaksi",
    "Kode_Obat",
    "Nama_Obat",
    "Stok_Awal",
    "Stok_Masuk",
    "Stok_Keluar",
    "Sisa_Stok",
    "Satuan",
]

pastikan_kolom(stok, kolom_stok_wajib, "data stok")

# Status boleh tidak tersedia pada file sumber.
if "Status" not in stok.columns:
    stok["Status"] = ""

stok = stok[kolom_stok_wajib + ["Status"]].copy()

for kolom in [
    "Waktu",
    "Nomor_Dokumen",
    "Jenis_Transaksi",
    "Kode_Obat",
    "Nama_Obat",
    "Satuan",
    "Status",
]:
    stok[kolom] = stok[kolom].fillna("").astype(str).str.strip()

for kolom in ["Stok_Awal", "Stok_Masuk", "Stok_Keluar", "Sisa_Stok"]:
    stok[kolom] = angka(stok[kolom])

stok["Tanggal_Transaksi"] = buat_tanggal(stok)
stok["Kategori_Transaksi"] = stok["Jenis_Transaksi"].apply(
    klasifikasi_transaksi
)

# ==========================================================
# 4. VALIDASI RUMUS PERGERAKAN STOK
# ==========================================================

stok["Sisa_Stok_Hasil_Hitung"] = (
    stok["Stok_Awal"] + stok["Stok_Masuk"] - stok["Stok_Keluar"]
)

stok["Selisih_Saldo"] = (
    stok["Sisa_Stok"] - stok["Sisa_Stok_Hasil_Hitung"]
)

stok["Validasi_Rumus_Stok"] = stok["Selisih_Saldo"].apply(
    lambda x: "Sesuai" if abs(x) < 0.000001 else "Perlu Diperiksa"
)

# ==========================================================
# 5. SIAPKAN KUNCI PENCOCOKAN PENJUALAN DAN STOK
# ==========================================================

# Pada data penjualan nama kolomnya Nomor_Nota.
# Pada data stok nama kolomnya Nomor_Dokumen.
# Samakan nama sementara agar dapat dicocokkan.

penjualan_untuk_cocok = penjualan.copy()

penjualan_untuk_cocok["Nomor_Dokumen"] = (
    penjualan_untuk_cocok["Nomor_Nota"]
)

# Hanya transaksi Penjualan dari data stok yang dicocokkan.
# Pembelian, retur, mutasi, dan transaksi lainnya tidak ikut.

stok_penjualan = stok[
    stok["Kategori_Transaksi"] == "Penjualan"
].copy()

kunci = [
    "Tanggal_Transaksi",
    "Nomor_Dokumen",
    "Kode_Obat",
]

# Kelompokkan data penjualan.
penjualan_cocok = (
    penjualan_untuk_cocok.groupby(kunci, dropna=False)
    .agg(
        Jumlah_Terjual=("Jumlah_Terjual", "sum"),
        Nama_Obat=("Nama_Obat", "first"),
        Satuan=("Satuan", "first"),
    )
    .reset_index()
)

# Kelompokkan data stok penjualan.
stok_cocok = (
    stok_penjualan.groupby(kunci, dropna=False)
    .agg(
        Jumlah_Stok_Keluar=("Stok_Keluar", "sum"),
        Nama_Obat_Stok=("Nama_Obat", "first"),
    )
    .reset_index()
)

# Gabungkan kedua data untuk pemeriksaan.
validasi_penjualan = penjualan_cocok.merge(
    stok_cocok,
    on=kunci,
    how="outer",
    indicator=True,
)

validasi_penjualan["Status_Pencocokan"] = "Ditemukan"

validasi_penjualan.loc[
    validasi_penjualan["_merge"] == "left_only",
    "Status_Pencocokan",
] = "Penjualan tidak ditemukan di data stok"

validasi_penjualan.loc[
    validasi_penjualan["_merge"] == "right_only",
    "Status_Pencocokan",
] = "Transaksi stok tidak ditemukan di data penjualan"

validasi_penjualan["Selisih_Jumlah"] = (
    validasi_penjualan["Jumlah_Terjual"].fillna(0)
    - validasi_penjualan["Jumlah_Stok_Keluar"].fillna(0)
)

validasi_penjualan.loc[
    (validasi_penjualan["_merge"] == "both")
    & (validasi_penjualan["Selisih_Jumlah"].abs() > 0.000001),
    "Status_Pencocokan",
] = "Jumlah penjualan berbeda dengan stok keluar"

# ==========================================================
# 6. AGREGASI PENJUALAN HARIAN PER OBAT
# ==========================================================

penjualan_harian = (
    penjualan[
        penjualan["Tanggal_Transaksi"].notna()
        & penjualan["Kode_Obat"].ne("")
        & (penjualan["Jumlah_Terjual"] > 0)
    ]
    .groupby(
        ["Tanggal_Transaksi", "Kode_Obat", "Nama_Obat", "Satuan"],
        as_index=False,
    )
    .agg(Jumlah_Terjual=("Jumlah_Terjual", "sum"))
)

penjualan_harian = penjualan_harian.rename(
    columns={"Tanggal_Transaksi": "Tanggal"}
)

penjualan_harian["Tahun"] = penjualan_harian["Tanggal"].dt.year
penjualan_harian["Bulan"] = penjualan_harian["Tanggal"].dt.month
penjualan_harian["Tanggal"] = (
    penjualan_harian["Tanggal"].dt.strftime("%Y-%m-%d")
)

penjualan_harian = penjualan_harian[
    [
        "Tahun",
        "Bulan",
        "Tanggal",
        "Kode_Obat",
        "Nama_Obat",
        "Jumlah_Terjual",
        "Satuan",
    ]
]


# ==========================================================
# 7. PEMERIKSAAN KESINAMBUNGAN SALDO STOK
# ==========================================================

stok_urut = stok.copy()

stok_urut = stok_urut.sort_values(
    [
        "Kode_Obat",
        "Tanggal_Transaksi",
        "Waktu",
        "Nomor_Dokumen",
    ],
    kind="stable",
)

stok_urut["Sisa_Stok_Sebelumnya"] = (
    stok_urut.groupby("Kode_Obat")["Sisa_Stok"].shift(1)
)

stok_urut["Selisih_Dengan_Transaksi_Sebelumnya"] = (
    stok_urut["Stok_Awal"] - stok_urut["Sisa_Stok_Sebelumnya"]
)

stok_urut["Validasi_Kesinambungan"] = "Transaksi pertama"

mask_sebelumnya = stok_urut["Sisa_Stok_Sebelumnya"].notna()

stok_urut.loc[
    mask_sebelumnya
    & (
        stok_urut["Selisih_Dengan_Transaksi_Sebelumnya"].abs()
        < 0.000001
    ),
    "Validasi_Kesinambungan",
] = "Sesuai"

stok_urut.loc[
    mask_sebelumnya
    & (
        stok_urut["Selisih_Dengan_Transaksi_Sebelumnya"].abs()
        >= 0.000001
    ),
    "Validasi_Kesinambungan",
] = "Perlu Diperiksa"

# Catatan: perbedaan saldo tidak otomatis berarti salah.
# Bisa ada transaksi lain, penyesuaian, atau data yang tidak lengkap.



# ==========================================================
# 8. SIMPAN HASIL
# ==========================================================

def siapkan_output_tanggal(df):
    """
    Menyeragamkan tanggal untuk seluruh file hasil.
    Format final: YYYY-MM-DD.
    Menghapus kolom Tahun, Bulan, dan Tanggal_Transaksi.
    """

    hasil = df.copy()

    # Gunakan Tanggal_Transaksi sebagai sumber tanggal utama
    if "Tanggal_Transaksi" in hasil.columns:
        hasil["Tanggal"] = pd.to_datetime(
            hasil["Tanggal_Transaksi"],
            errors="coerce"
        ).dt.strftime("%Y-%m-%d")

        hasil = hasil.drop(columns=["Tanggal_Transaksi"])

    # Hilangkan kolom terpisah karena sudah tercakup dalam Tanggal
    hasil = hasil.drop(
        columns=["Tahun", "Bulan"],
        errors="ignore"
    )

    return hasil


# ----------------------------------------------------------
# 8.1 DATA PENJUALAN BERSIH
# ----------------------------------------------------------

penjualan_output = siapkan_output_tanggal(penjualan)

penjualan_output.to_csv(
    OUTPUT / "penjualan_bersih.csv",
    index=False,
    encoding="utf-8-sig"
)


# ----------------------------------------------------------
# 8.2 DATA PENJUALAN HARIAN UNTUK PERAMALAN
# ----------------------------------------------------------

penjualan_harian_output = siapkan_output_tanggal(
    penjualan_harian
)

penjualan_harian_output.to_csv(
    OUTPUT / "penjualan_harian.csv",
    index=False,
    encoding="utf-8-sig"
)


# ----------------------------------------------------------
# 8.3 RIWAYAT PERGERAKAN STOK
# ----------------------------------------------------------

stok_output = siapkan_output_tanggal(stok)

stok_output.to_csv(
    OUTPUT / "riwayat_pergerakan_stok.csv",
    index=False,
    encoding="utf-8-sig"
)


# ----------------------------------------------------------
# 8.4 VALIDASI KESINAMBUNGAN STOK
# ----------------------------------------------------------

stok_urut_output = siapkan_output_tanggal(stok_urut)

stok_urut_output.to_csv(
    OUTPUT / "validasi_kesinambungan_stok.csv",
    index=False,
    encoding="utf-8-sig"
)


# ----------------------------------------------------------
# 8.5 VALIDASI PENJUALAN DAN STOK
# ----------------------------------------------------------

validasi_penjualan_output = siapkan_output_tanggal(
    validasi_penjualan
)

validasi_penjualan_output.to_csv(
    OUTPUT / "validasi_penjualan_dan_stok.csv",
    index=False,
    encoding="utf-8-sig"
)


# ----------------------------------------------------------
# 8.6 TRANSAKSI YANG PERLU DIPERIKSA
# ----------------------------------------------------------

transaksi_perlu_diperiksa = stok[
    stok["Kategori_Transaksi"] == "Perlu Diperiksa"
].copy()

transaksi_perlu_diperiksa_output = siapkan_output_tanggal(
    transaksi_perlu_diperiksa
)

transaksi_perlu_diperiksa_output.to_csv(
    OUTPUT / "transaksi_perlu_diperiksa.csv",
    index=False,
    encoding="utf-8-sig"
)


# ----------------------------------------------------------
# 8.7 RINGKASAN JENIS TRANSAKSI
# ----------------------------------------------------------

ringkasan_transaksi = (
    stok.groupby("Kategori_Transaksi")
    .agg(
        Jumlah_Baris=("Kode_Obat", "size"),
        Jumlah_Kode_Obat=("Kode_Obat", "nunique"),
    )
    .reset_index()
)

ringkasan_transaksi.to_csv(
    OUTPUT / "ringkasan_jenis_transaksi.csv",
    index=False,
    encoding="utf-8-sig"
)


# ----------------------------------------------------------
# 8.8 RINGKASAN VALIDASI RUMUS STOK
# ----------------------------------------------------------

ringkasan_rumus = (
    stok["Validasi_Rumus_Stok"]
    .value_counts()
    .rename_axis("Hasil_Validasi")
    .reset_index(name="Jumlah_Baris")
)

ringkasan_rumus.to_csv(
    OUTPUT / "ringkasan_validasi_rumus_stok.csv",
    index=False,
    encoding="utf-8-sig"
)


# ==========================================================
# 9. INFORMASI HASIL PENGOLAHAN
# ==========================================================

print("\nPENGOLAHAN SELESAI")

print(f"Lokasi hasil: {OUTPUT}")

print(f"Jumlah baris penjualan: {len(penjualan):,}")

print(f"Jumlah baris stok: {len(stok):,}")

print(
    "Jumlah baris penjualan harian: "
    f"{len(penjualan_harian):,}"
)

print("\nRingkasan jenis transaksi:")

print(ringkasan_transaksi.to_string(index=False))

print("\nRingkasan validasi rumus stok:")

print(ringkasan_rumus.to_string(index=False))

print("\nFile berhasil disimpan dengan format tanggal YYYY-MM-DD.")
