import sys
import os
import copy
import pptx
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.enum.shapes import MSO_SHAPE

def create_presentation():
    template_path = 'TEMPLATE PPT TUGAS AKHIR - FASILKOM.pptx'
    output_path = 'Presentasi_TA_DSH_20_Slides.pptx'

    prs = Presentation(template_path)
    blank_layout = prs.slide_layouts[0]

    orig_slides = list(prs.slides)
    num_orig = len(orig_slides)

    # 20 Slide mappings
    slide_sources = [
        0,  # Slide 1  (Cover T1)
        1,  # Slide 2  (Latar Belakang T2)
        1,  # Slide 3  (Masalah & Tujuan T2)
        6,  # Slide 4  (Batasan & Manfaat T7)
        2,  # Slide 5  (Studi Literatur T3)
        2,  # Slide 6  (Research Gap & Novelty T3)
        3,  # Slide 7  (Teori: Monitoring & EDA T4)
        3,  # Slide 8  (Teori: Temporal T4)
        3,  # Slide 9  (Teori: Stack & Metode T4)
        4,  # Slide 10 (DSR 6 Tahap T5)
        5,  # Slide 11 (Tahapan & 5 Increment T6)
        6,  # Slide 12 (Objek & Data T7)
        7,  # Slide 13 (Proses Berjalan T8)
        7,  # Slide 14 (Proses Diusulkan T8)
        8,  # Slide 15 (Arsitektur Sistem T9)
        8,  # Slide 16 (Modul & Skema Data T9)
        7,  # Slide 17 (Implementasi & Pengujian T8)
        9,  # Slide 18 (Hasil & Pembahasan T10)
        10, # Slide 19 (Kesimpulan & Pustaka T11)
        11  # Slide 20 (Terima Kasih T12)
    ]

    new_slides = []
    for src_idx in slide_sources:
        src_slide = orig_slides[src_idx]
        new_slide = prs.slides.add_slide(blank_layout)
        for shape in src_slide.shapes:
            el = copy.deepcopy(shape.element)
            new_slide.shapes._spTree.append(el)
        new_slides.append(new_slide)

    # Hapus 12 slide asli
    for _ in range(num_orig):
        rId = prs.slides._sldIdLst[0].rId
        prs.part.drop_rel(rId)
        del prs.slides._sldIdLst[0]

    # Palet Warna Profesional & Medis
    COLOR_NAVY = RGBColor(0x28, 0x29, 0x5D)      # Warna Utama Template Fasilkom
    COLOR_TEAL = RGBColor(0x20, 0x59, 0x6C)      # Warna Resmi Klinik DSH
    COLOR_TEAL_DARK = RGBColor(0x13, 0x3E, 0x4B) # Dark Teal
    COLOR_WHITE = RGBColor(0xFF, 0xFF, 0xFF)     # Putih Bersih
    COLOR_BG_CARD = RGBColor(0xF4, 0xF8, 0xFA)   # Card Fill Lembut Soft Teal
    COLOR_BG_WHITE = RGBColor(0xFC, 0xFD, 0xFE)  # Card Fill Putih
    COLOR_BORDER_TEAL = RGBColor(0xBE, 0xDD, 0xE5) # Border Card Teal Lembut
    COLOR_BORDER_NAVY = RGBColor(0xD0, 0xD4, 0xEB) # Border Card Navy Lembut
    COLOR_TEXT_MAIN = RGBColor(0x1E, 0x29, 0x3B) # Charcoal Gelap Sangat Terbaca
    COLOR_TEXT_MUTED = RGBColor(0x47, 0x55, 0x69)# Slate Muted
    COLOR_AMBER = RGBColor(0xB4, 0x53, 0x09)     # Aksen Amber
    COLOR_GREEN = RGBColor(0x06, 0x5F, 0x46)     # Aksen Hijau Sukses

    FONT_MAIN = 'Plus Jakarta Sans'
    FONT_HEAD = 'Poppins'

    def clear_tf(tf):
        tf.word_wrap = True
        for p in tf.paragraphs:
            p.text = ""

    # Helper: Membuat Visual Container Card (Rounded Rectangle dengan Border & Shadow Effect)
    def add_card(slide, left, top, width, height, bg_color=COLOR_BG_WHITE, border_color=COLOR_BORDER_TEAL, border_width=1.5):
        shape = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        shape.fill.solid()
        shape.fill.fore_color.rgb = bg_color
        shape.line.color.rgb = border_color
        shape.line.width = Pt(border_width)
        return shape

    # Helper: Menambahkan Judul / Header Slide
    def set_slide_title(slide, text, top=Inches(0.7), height=Inches(1.2)):
        box = slide.shapes.add_textbox(Inches(1.2), top, Inches(17.6), height)
        tf = box.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.alignment = PP_ALIGN.LEFT
        r = p.add_run()
        r.text = text
        r.font.name = FONT_HEAD
        r.font.size = Pt(23)
        r.font.bold = True
        r.font.color.rgb = COLOR_NAVY
        return box

    # Helper: Menambahkan Teks Poin di dalam TextFrame
    def add_point(tf, title, desc="", level=0, pt_size=14.5, is_bold=True, tag_color=COLOR_TEAL):
        p = tf.add_paragraph() if tf.paragraphs[0].text else tf.paragraphs[0]
        p.space_after = Pt(8)
        p.line_spacing = 1.15
        
        # Bullet Icon
        r_b = p.add_run()
        r_b.text = "▪ " if level == 0 else "   ▫ "
        r_b.font.name = FONT_MAIN
        r_b.font.size = Pt(pt_size)
        r_b.font.bold = True
        r_b.font.color.rgb = tag_color

        # Title
        r_t = p.add_run()
        r_t.text = title + (" " if desc else "")
        r_t.font.name = FONT_MAIN
        r_t.font.size = Pt(pt_size)
        r_t.font.bold = is_bold
        r_t.font.color.rgb = COLOR_NAVY if level == 0 else COLOR_TEXT_MAIN

        # Description
        if desc:
            r_d = p.add_run()
            r_d.text = desc
            r_d.font.name = FONT_MAIN
            r_d.font.size = Pt(pt_size)
            r_d.font.bold = False
            r_d.font.color.rgb = COLOR_TEXT_MAIN

    # =========================================================================
    # SLIDE 1: COVER
    # =========================================================================
    s1 = prs.slides[0]
    # Judul
    s1_title = s1.shapes[3]
    clear_tf(s1_title.text_frame)
    p = s1_title.text_frame.paragraphs[0]
    p.line_spacing = 1.15
    p.alignment = PP_ALIGN.LEFT
    r = p.add_run()
    r.text = "IMPLEMENTASI EVENT-DRIVEN ARCHITECTURE DENGAN DURABLE WORKFLOW ORCHESTRATION MENGGUNAKAN TEMPORAL PADA SISTEM MONITORING PERKEMBANGAN PASIEN"
    r.font.name = FONT_HEAD
    r.font.size = Pt(21)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY

    # Dosen Pembimbing
    s1_dosen = s1.shapes[7]
    clear_tf(s1_dosen.text_frame)
    p = s1_dosen.text_frame.paragraphs[0]
    r = p.add_run()
    r.text = "Dr. Riya Widayanti, S.Kom., M.M.S.I."
    r.font.name = FONT_HEAD
    r.font.size = Pt(15.5)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL

    # Identitas Mahasiswa
    s1_mhs = s1.shapes[8]
    clear_tf(s1_mhs.text_frame)
    p1 = s1_mhs.text_frame.paragraphs[0]
    r1 = p1.add_run()
    r1.text = "MUHAMAD ROHISUL IMAN\n"
    r1.font.name = FONT_HEAD
    r1.font.size = Pt(17.5)
    r1.font.bold = True
    r1.font.color.rgb = COLOR_NAVY

    r2 = p1.add_run()
    r2.text = "NIM : 20230801270\n"
    r2.font.name = FONT_MAIN
    r2.font.size = Pt(15)
    r2.font.bold = True
    r2.font.color.rgb = COLOR_TEAL

    r3 = p1.add_run()
    r3.text = "Program Studi Teknik Informatika\nFakultas Ilmu Komputer, Universitas Esa Unggul\nObjek Penelitian: Klinik Darus Syifa Holistik (DSH)\nTahun: 2026-2027"
    r3.font.name = FONT_MAIN
    r3.font.size = Pt(13)
    r3.font.color.rgb = COLOR_TEXT_MUTED

    # =========================================================================
    # SLIDE 2: LATAR BELAKANG PENELITIAN (3 Visual Cards)
    # =========================================================================
    s2 = prs.slides[1]
    # Sembunyikan shape teks acak bawaan
    clear_tf(s2.shapes[9].text_frame)
    clear_tf(s2.shapes[10].text_frame)

    set_slide_title(s2, "LATAR BELAKANG DAN URGENSI PENELITIAN")

    # Card 1: Kondisi Objek & Permasalahan
    c1 = add_card(s2, Inches(1.2), Inches(2.2), Inches(5.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf1 = c1.text_frame
    tf1.word_wrap = True
    p = tf1.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Kondisi Eksisting Klinik DSH\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(17)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf1, "Pencatatan Masih Manual:", "Seluruh data pemeriksaan, evaluasi, dan rekam klinis pasien masih dicatat di file Excel terpisah.", pt_size=13.5)
    add_point(tf1, "Dua Jenis Pasien Berbeda:", "Melayani Pasien Umum (kunjungan bebas) dan Pasien Program MHLS (program terstruktur 12 pertemuan).", pt_size=13.5)
    add_point(tf1, "Keterbatasan Excel:", "Sulit mendeteksi anomali tren kesehatan dan rentan inkonsistensi data riwayat kunjungan.", pt_size=13.5)

    # Card 2: Karakteristik Program MHLS
    c2 = add_card(s2, Inches(7.2), Inches(2.2), Inches(5.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf2 = c2.text_frame
    tf2.word_wrap = True
    p = tf2.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Karakteristik Program MHLS\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(17)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL
    add_point(tf2, "Siklus Panjang 3 Bulan:", "Pasien menjalani 12 kali pertemuan klinis terjadwal secara bertahap.", pt_size=13.5)
    add_point(tf2, "Aktivitas Mandiri di Rumah:", "Di antara jadwal pertemuan, pasien wajib menjalankan instruksi puasa, real food, dan istirahat.", pt_size=13.5)
    add_point(tf2, "Evaluasi Tertunda:", "Saat ini evaluasi kepatuhan baru bisa dilakukan secara kuis tanya-jawab saat pasien datang kembali.", pt_size=13.5)

    # Card 3: Solusi yang Diusulkan
    c3 = add_card(s2, Inches(13.2), Inches(2.2), Inches(5.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf3 = c3.text_frame
    tf3.word_wrap = True
    p = tf3.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Solusi EDA & Temporal\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(17)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf3, "Aplikasi Web & Mobile:", "Web untuk tata kelola klinik dan mobile app untuk pelaporan mandiri pasien secara berkala.", pt_size=13.5)
    add_point(tf3, "Event-Driven (EDA):", "Menghubungkan proses pendaftaran, jadwal, dan rekam klinis secara asinkron bebas ketergantungan erat.", pt_size=13.5)
    add_point(tf3, "Temporal Orchestrator:", "Mengelola timer pengingat otomatis dan menjamin alur pemantauan tetap berjalan tahan gangguan.", pt_size=13.5)

    # =========================================================================
    # SLIDE 3: MASALAH, RUMUSAN & TUJUAN PENELITIAN (2 Large Structured Cards)
    # =========================================================================
    s3 = prs.slides[2]
    clear_tf(s3.shapes[6].text_frame)
    clear_tf(s3.shapes[9].text_frame)
    clear_tf(s3.shapes[10].text_frame)

    set_slide_title(s3, "RUMUSAN MASALAH DAN TUJUAN PENELITIAN")

    # Kolom Kiri: Rumusan Masalah
    c_rm = add_card(s3, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf_rm = c_rm.text_frame
    tf_rm.word_wrap = True
    p = tf_rm.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Rumusan Masalah (RM)\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY

    add_point(tf_rm, "RM-1 (Rancang Bangun Sistem):", "Bagaimana merancang dan membangun sistem monitoring perkembangan pasien berbasis web dan mobile sesuai kebutuhan Klinik DSH?", pt_size=13.5)
    add_point(tf_rm, "RM-2 (Penerapan EDA):", "Bagaimana menerapkan Event-Driven Architecture untuk menghubungkan dan mengintegrasikan proses antarbagian sistem melalui pertukaran event?", pt_size=13.5)
    add_point(tf_rm, "RM-3 (Orkestrasi Temporal):", "Bagaimana menerapkan Durable Workflow Orchestration menggunakan Temporal untuk mengelola alur monitoring pasien MHLS yang berlangsung dalam jangka waktu tertentu?", pt_size=13.5)
    add_point(tf_rm, "RM-4 (Pengujian & Ketahanan):", "Bagaimana hasil pengujian fungsionalitas dan keandalan sistem dalam memproses event serta mempertahankan workflow setelah terjadi gangguan worker?", pt_size=13.5)

    # Kolom Kanan: Tujuan Penelitian
    c_tj = add_card(s3, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf_tj = c_tj.text_frame
    tf_tj.word_wrap = True
    p = tf_tj.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Tujuan Penelitian (T)\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL

    add_point(tf_tj, "T-1 (Membangun Solusi Terpadu):", "Merancang dan membangun aplikasi web admin klinik serta aplikasi mobile pasien yang mencakup 5 modul operasional DSH.", pt_size=13.5)
    add_point(tf_tj, "T-2 (Implementasi Event Asinkron):", "Menerapkan EDA dengan Transactional Outbox Pattern guna menjamin pengiriman event antarmodul secara decoupled dan konsisten.", pt_size=13.5)
    add_point(tf_tj, "T-3 (Implementasi Workflow MHLS):", "Mengimplementasikan Temporal untuk mengotomasi alur program 12 pertemuan, timer reminder mandiri, dan penanganan sinyal laporan pasien.", pt_size=13.5)
    add_point(tf_tj, "T-4 (Evaluasi Fungsional & Failover):", "Mengevaluasi sistem menggunakan Black Box Testing dan membuktikan kemampuan pemulihan (recovery) workflow pasca-gangguan worker.", pt_size=13.5)

    # =========================================================================
    # SLIDE 4: BATASAN DAN MANFAAT PENELITIAN (2 Cards)
    # =========================================================================
    s4 = prs.slides[3]
    clear_tf(s4.shapes[2].text_frame)
    clear_tf(s4.shapes[13].text_frame)

    set_slide_title(s4, "BATASAN DAN MANFAAT PENELITIAN")

    # Kiri: Batasan
    c_bat = add_card(s4, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf_bat = c_bat.text_frame
    tf_bat.word_wrap = True
    p = tf_bat.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Ruang Lingkup & Batasan Penelitian\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY

    add_point(tf_bat, "Objek Penelitian:", "Proses pemantauan klinis perkembangan pasien di Klinik Darus Syifa Holistik (DSH).", pt_size=14)
    add_point(tf_bat, "Platform yang Dibangun:", "Web Admin untuk petugas klinik & Mobile App untuk pasien peserta program MHLS.", pt_size=14)
    add_point(tf_bat, "Cakupan 5 Modul:", "Pendaftaran Pasien, Jadwal & Kunjungan, Pemeriksaan Klinis, Monitoring Perkembangan, & Aktivitas Mandiri.", pt_size=14)
    add_point(tf_bat, "Fokus Arsitektur:", "EDA untuk pertukaran event dan Temporal untuk orkestrasi workflow aktivitas mandiri.", pt_size=14)
    add_point(tf_bat, "Batasan Medis & AI:", "Tidak mencakup diagnosis penyakit otomatis, auto-anamnesis, atau rekomendasi berbasis machine learning.", pt_size=14)
    add_point(tf_bat, "Batasan Sistem Eksternal:", "Tidak mencakup integrasi sistem farmasi obat, kasir/billing, atau BPJS/rekam medis RS luar.", pt_size=14)

    # Kanan: Manfaat
    c_man = add_card(s4, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf_man = c_man.text_frame
    tf_man.word_wrap = True
    p = tf_man.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Manfaat Penelitian\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL

    add_point(tf_man, "Manfaat Teoretis:", "Menjadi rujukan ilmiah mengenai penggabungan EDA dan Temporal pada sistem layanan kesehatan berbasis workflow berdurasi panjang.", pt_size=14)
    add_point(tf_man, "Manfaat Praktis Klinik DSH:", "Mengeliminasi pencatatan manual spreadsheet, mempercepat rekam 5 kelompok data, serta menyediakan grafik analitis terpadu.", pt_size=14)
    add_point(tf_man, "Manfaat Pasien MHLS:", "Mempermudah pasien mengajukan jadwal kunjungan dari smartphone, memantau grafik perkembangan, dan disiplin mengisi laporan harian.", pt_size=14)
    add_point(tf_man, "Manfaat Teknis (Software Engineering):", "Menghasilkan arsitektur perangkat lunak yang tangguh (fault-tolerant) dan menjamin data consistency tanpa risiko dual-write.", pt_size=14)

    # =========================================================================
    # SLIDE 5: STUDI LITERATUR (Clean Modern Table Container)
    # =========================================================================
    s5 = prs.slides[4]
    clear_tf(s5.shapes[2].text_frame)
    set_slide_title(s5, "PENELITIAN TERDAHULU DAN LANDASAN KOMPARASI")

    # Background card container for table
    add_card(s5, Inches(1.0), Inches(2.0), Inches(18.0), Inches(7.3), COLOR_BG_WHITE, COLOR_BORDER_NAVY)

    table_shape = s5.shapes.add_table(9, 4, Inches(1.2), Inches(2.2), Inches(17.6), Inches(6.8))
    tbl = table_shape.table
    tbl.columns[0].width = Inches(3.6)
    tbl.columns[1].width = Inches(4.5)
    tbl.columns[2].width = Inches(4.8)
    tbl.columns[3].width = Inches(4.7)

    headers = ["Peneliti & Tahun", "Fokus Penelitian", "Temuan Utama / Kontribusi", "Relevansi dengan Tugas Akhir"]
    for c_idx, h in enumerate(headers):
        cell = tbl.cell(0, c_idx)
        cell.fill.solid()
        cell.fill.fore_color.rgb = COLOR_TEAL
        p = cell.text_frame.paragraphs[0]
        p.alignment = PP_ALIGN.CENTER
        r = p.add_run()
        r.text = h
        r.font.name = FONT_HEAD
        r.font.size = Pt(13)
        r.font.bold = True
        r.font.color.rgb = COLOR_WHITE

    lit_data = [
        ("Soegaard Ballester et al. (2021)", "Aplikasi alur kerja tim pelayanan rawat inap", "Diadopsi di 152 dari 169 layanan RS; koordinasi tim meningkat", "Bukti digitalisasi mendukung koordinasi tahapan layanan klinis"),
        ("Stratil et al. (2026)", "Aplikasi kesehatan Brisa untuk pencatatan gejala", "76% dari 4.194 pengguna aktif mencatat gejala mandiri via aplikasi", "Menegaskan urgensi aplikasi mobile untuk rekam kondisi pasien"),
        ("Nowell et al. (2023)", "Pemantauan longitudinal & pengingat berkala", "Kepatuhan pengisian mingguan 87,4% berkat sistem reminder", "Dasar perlunya pengingat otomatis aktivitas mandiri pasien MHLS"),
        ("Syarif & Pizaini (2022)", "Event-Driven Architecture pada sistem PPDB", "Komunikasi antarlayanan terintegrasi melalui pertukaran event", "Landasan penerapan EDA untuk komunikasi asinkron antarmodul"),
        ("Laigner et al. (2026)", "Tantangan pengelolaan event pada microservices", "Identifikasi risiko tracing, konsistensi data, dan status proses", "Dasar perlunya arsitektur terstruktur (Transactional Outbox)"),
        ("Bação & Guerreiro (2026)", "Konsistensi data pada event-driven microservices", "Sistem event-driven butuh mekanisme penjaminan konsistensi state", "Memperkuat keputusan memisahkan domain event dan durable state"),
        ("Nagpal (2026)", "Komparasi teknologi Workflow Orchestration", "Temporal unggul dalam state persistence dan recovery saat worker mati", "Bukti ilmiah pemilihan Temporal untuk mengelola workflow jangka panjang"),
        ("Yasini et al. (2025)", "Klasifikasi Clinical Events pelayanan kesehatan", "Pemetaan 168 jenis clinical events dalam 9 domain pelayanan", "Mendukung representasi tahapan klinis DSH berbasis event"),
    ]

    for r_idx, row in enumerate(lit_data):
        for c_idx, val in enumerate(row):
            cell = tbl.cell(r_idx + 1, c_idx)
            cell.fill.solid()
            cell.fill.fore_color.rgb = COLOR_BG_WHITE if r_idx % 2 == 0 else COLOR_BG_CARD
            p = cell.text_frame.paragraphs[0]
            p.line_spacing = 1.15
            r = p.add_run()
            r.text = val
            r.font.name = FONT_MAIN
            r.font.size = Pt(11.5)
            r.font.bold = (c_idx == 0)
            r.font.color.rgb = COLOR_NAVY if c_idx == 0 else COLOR_TEXT_MAIN

    # =========================================================================
    # SLIDE 6: RESEARCH GAP DAN NOVELTY (2 Cards)
    # =========================================================================
    s6 = prs.slides[5]
    clear_tf(s6.shapes[2].text_frame)
    set_slide_title(s6, "RESEARCH GAP DAN NOVELTY PENELITIAN")

    # Card Gap
    c_gap = add_card(s6, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf_gap = c_gap.text_frame
    tf_gap.word_wrap = True
    p = tf_gap.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Research Gap (Kesenjangan Penelitian)\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY

    add_point(tf_gap, "Sistem Monitoring Kesehatan Saat Ini:", "Studi terdahulu (Stratil 2026, Nowell 2023) fokus pada formulir pencatatan statis, belum ada mekanisme durable workflow untuk program berdurasi panjang.", pt_size=14)
    add_point(tf_gap, "Keterbatasan Komunikasi EDA Murni:", "Penelitian EDA (Syarif 2022, Laigner 2026) efektif untuk decoupling pesan instan, namun kesulitan mengelola status alur kerja pasien yang berlangsung selama 3 bulan.", pt_size=14)
    add_point(tf_gap, "Belum Ada Integrasi EDA + Temporal:", "Berdasarkan telaah pustaka ilmiah terkini, belum ditemukan penelitian yang menggabungkan EDA dan Temporal secara terpadu pada sistem pemantauan klinis.", pt_size=14)

    # Card Novelty
    c_nov = add_card(s6, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf_nov = c_nov.text_frame
    tf_nov.word_wrap = True
    p = tf_nov.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Kebaruan Penelitian (Novelty)\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL

    add_point(tf_nov, "Pola Sinergis EDA + Temporal:", "Memadukan EDA (Transactional Outbox) untuk komunikasi antar-modul asinkron dan Temporal untuk orkestrasi workflow aktivitas mandiri berdurasi panjang.", pt_size=14)
    add_point(tf_nov, "Durable State Execution:", "Status eksekusi 12 sesi program dijamin persisten; workflow mampu melanjutkan timer dan sinyal secara otomatis meski worker mengalami restart/kegagalan sistem.", pt_size=14)
    add_point(tf_nov, "Penerapan Nyata di Klinik Holistik:", "Diterapkan langsung pada protokol klinis Moslem Healthy Lifestyle (DSH) dengan pencatatan terstruktur 5 kelompok data dan 6 analisis grafik perkembangan.", pt_size=14)

    # =========================================================================
    # SLIDE 7: TEORI: MONITORING & EDA (2 Cards)
    # =========================================================================
    s7 = prs.slides[6]
    set_slide_title(s7, "LANDASAN TEORI: MONITORING & EVENT-DRIVEN ARCHITECTURE")

    c7_1 = add_card(s7, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf7_1 = c7_1.text_frame
    tf7_1.word_wrap = True
    p = tf7_1.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Konsep Monitoring Klinis Terstruktur\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf7_1, "Definisi Monitoring:", "Pemantauan berkala parameter kesehatan pasien untuk mengevaluasi efektivitas terapi holistik dari waktu ke waktu.", pt_size=14)
    add_point(tf7_1, "Objektivitas Parameter:", "Menggantikan catatan deskriptif bebas dengan 5 kelompok parameter terukur (Beban Toksin 0-50, Antropometri Tubuh, Tekanan Darah, Lab Metabolik, dan Rasio GKI).", pt_size=14)
    add_point(tf7_1, "Visualisasi Tren Klinis:", "Penyajian data sesi ke sesi dalam bentuk kurva analitis untuk memudahkan dokter/terapis mengambil keputusan evaluasi.", pt_size=14)

    c7_2 = add_card(s7, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf7_2 = c7_2.text_frame
    tf7_2.word_wrap = True
    p = tf7_2.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Event-Driven Architecture & Outbox Pattern\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL
    add_point(tf7_2, "Konsep Domain Event:", "Peristiwa penting yang terjadi dalam domain bisnis (misal: PasienTerdaftar, KunjunganDikonfirmasi, SesiPemeriksaanDicatat).", pt_size=14)
    add_point(tf7_2, "Komponen EDA:", "Event Producer (backend), Event Broker/Queue, dan Event Consumer/Handler.", pt_size=14)
    add_point(tf7_2, "Transactional Outbox Pattern:", "Event disimpan ke tabel outbox dalam database transaction atomic yang sama dengan data bisnis, mencegah dual-write failure.", pt_size=14)
    add_point(tf7_2, "Keuntungan:", "Loose coupling antar-modul, sistem lebih responsif, dan rekam jejak audit (event log) yang lengkap.", pt_size=14)

    # =========================================================================
    # SLIDE 8: TEORI: TEMPORAL & DURABLE WORKFLOW (2 Cards)
    # =========================================================================
    s8 = prs.slides[7]
    set_slide_title(s8, "LANDASAN TEORI: TEMPORAL & DURABLE WORKFLOW")

    c8_1 = add_card(s8, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf8_1 = c8_1.text_frame
    tf8_1.word_wrap = True
    p = tf8_1.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Prinsip Durable Workflow Orchestration\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf8_1, "Workflow Jangka Panjang:", "Alur proses bisnis yang berjalan melintasi hari, minggu, hingga berbulan-bulan tanpa memblokir thread sistem.", pt_size=14)
    add_point(tf8_1, "Durable Execution:", "Status eksekusi (state) alur kerja disimpan secara persisten; proses dapat dihentikan dan dilanjutkan kapan saja tanpa kehilangan konteks.", pt_size=14)
    add_point(tf8_1, "Event Sourcing History:", "Setiap aksi dicatat sebagai append-only event log; pemulihan proses dilakukan dengan memutar ulang riwayat (replay).", pt_size=14)

    c8_2 = add_card(s8, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf8_2 = c8_2.text_frame
    tf8_2.word_wrap = True
    p = tf8_2.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Komponen & Kapabilitas Temporal\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL
    add_point(tf8_2, "Temporal Server & Cluster:", "Mengelola task queue, history persisten, dan penjadwalan eksekusi alur kerja.", pt_size=14)
    add_point(tf8_2, "Temporal Worker:", "Layanan background yang mengeksekusi kode alur kerja (workflow) dan pemanggilan fungsi (activity).", pt_size=14)
    add_point(tf8_2, "Durable Timer:", "Fitur penundaan eksekusi hingga jadwal pengingat tiba tanpa mengonsumsi memori atau thread aktif.", pt_size=14)
    add_point(tf8_2, "Signal & Query:", "Menerima laporan aktivitas pasien asinkron (Signal) dan memeriksa kepatuhan tanpa mengubah status alur (Query).", pt_size=14)

    # =========================================================================
    # SLIDE 9: TEORI: TEKNOLOGI STACK & METODE (2 Cards)
    # =========================================================================
    s9 = prs.slides[8]
    set_slide_title(s9, "LANDASAN TEORI: TEKNOLOGI STACK & METODE PENGEMBANGAN")

    c9_1 = add_card(s9, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf9_1 = c9_1.text_frame
    tf9_1.word_wrap = True
    p = tf9_1.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Arsitektur Teknologi yang Digunakan\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf9_1, "Backend Framework (Laravel):", "Penyedia REST API untuk aplikasi mobile dan antarmuka web klinik dengan Eloquent ORM dan Service Layer.", pt_size=14)
    add_point(tf9_1, "Basis Data Relasional (MySQL 8.0):", "Menyimpan data pasien, riwayat 5 kelompok klinis, jadwal kunjungan, tabel outbox, dan referensi workflow.", pt_size=14)
    add_point(tf9_1, "Aplikasi Mobile (Flutter):", "Aplikasi lintas platform bagi pasien MHLS untuk mengajukan booking dan mengisi checklist aktivitas mandiri harian.", pt_size=14)
    add_point(tf9_1, "Kontainerisasi (Docker Compose):", "Mengisolasi seluruh layanan (backend, database, Temporal server, worker, outbox dispatcher) dalam lingkungan konsisten.", pt_size=14)

    c9_2 = add_card(s9, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf9_2 = c9_2.text_frame
    tf9_2.word_wrap = True
    p = tf9_2.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Metode Rekayasa & Evaluasi\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL
    add_point(tf9_2, "Design Science Research (DSR):", "Pendekatan metodologi ilmiah untuk merancang, mengimplementasikan, dan mengevaluasi artefak teknologi pemecah masalah nyata.", pt_size=14)
    add_point(tf9_2, "Model Pengembangan Inkremental:", "Pengembangan bertahap berbasis modul layanan (Pendaftaran &rarr; Jadwal &rarr; Pemeriksaan &rarr; Monitoring &rarr; Aktivitas Mandiri).", pt_size=14)
    add_point(tf9_2, "Metode Evaluasi Fungsional:", "Black Box Testing untuk menguji fungsionalitas tombol, validasi formulir, dan integritas alur bisnis.", pt_size=14)
    add_point(tf9_2, "Uji Ketahanan (Resilience Testing):", "Pengujian failover dengan mematikan container worker secara mendadak saat workflow aktif.", pt_size=14)

    # =========================================================================
    # SLIDE 10: METODOLOGI PENELITIAN DSR (6 Cards Grid)
    # =========================================================================
    s10 = prs.slides[9]
    clear_tf(s10.shapes[4].text_frame)
    set_slide_title(s10, "METODOLOGI PENELITIAN: 6 TAHAP DSR")

    dsr_stages = [
        ("1. Identifikasi Masalah", "Monitoring 12 sesi MHLS di Klinik DSH masih manual Excel dan sulit dipantau di luar klinik.", COLOR_NAVY),
        ("2. Penentuan Solusi", "Menetapkan sasaran sistem web & mobile berbasis EDA dan Temporal untuk otomatisasi alur pemantauan.", COLOR_TEAL),
        ("3. Desain & Pengembangan", "Merancang skema database, transactional outbox, dan orkestrasi workflow aktivitas mandiri.", COLOR_NAVY),
        ("4. Demonstrasi Sistem", "Mengoperasikan sistem terintegrasi (web klinik, mobile app, backend API, Temporal, dan database).", COLOR_TEAL),
        ("5. Evaluasi & Pengujian", "Melakukan Black Box Testing fungsionalitas dan uji failover ketahanan worker Temporal saat mati.", COLOR_NAVY),
        ("6. Komunikasi Hasil", "Mempresentasikan tugas akhir dan mendokumentasikan kode, arsitektur, serta laporan ilmiah.", COLOR_TEAL),
    ]

    card_w = Inches(8.6)
    card_h = Inches(2.1)
    for idx, (st_title, st_desc, col) in enumerate(dsr_stages):
        r_i = idx // 2
        c_i = idx % 2
        l_pos = Inches(1.2) if c_i == 0 else Inches(10.2)
        t_pos = Inches(2.2) + r_i * Inches(2.4)
        bg = COLOR_BG_WHITE if c_i == 0 else COLOR_BG_CARD
        bdr = COLOR_BORDER_NAVY if c_i == 0 else COLOR_BORDER_TEAL
        cd = add_card(s10, l_pos, t_pos, card_w, card_h, bg, bdr)
        t_f = cd.text_frame
        t_f.word_wrap = True
        p = t_f.paragraphs[0]
        r = p.add_run()
        r.text = st_title + "\n"
        r.font.name = FONT_HEAD
        r.font.size = Pt(16)
        r.font.bold = True
        r.font.color.rgb = col
        
        p2 = t_f.add_paragraph()
        p2.line_spacing = 1.15
        r2 = p2.add_run()
        r2.text = st_desc
        r2.font.name = FONT_MAIN
        r2.font.size = Pt(13)
        r2.font.color.rgb = COLOR_TEXT_MAIN

    # =========================================================================
    # SLIDE 11: TAHAPAN PENELITIAN & 5 INCREMENT (5 Horizontal Cards)
    # =========================================================================
    s11 = prs.slides[10]
    clear_tf(s11.shapes[2].text_frame)
    set_slide_title(s11, "TAHAPAN PENELITIAN & 5 INCREMENT PENGEMBANGAN")

    increments = [
        ("Increment 1 - Data Pasien:", "Pengelolaan data induk pasien, penerbitan No RM otomatis, serta pemisahan kategori Pasien Umum vs Program MHLS.", COLOR_NAVY),
        ("Increment 2 - Jadwal & Kunjungan:", "Sinkronisasi antrean klinik dengan pengajuan booking 12 pertemuan MHLS dari aplikasi mobile pasien.", COLOR_TEAL),
        ("Increment 3 - Pemeriksaan Klinis:", "Pencatatan 5 kelompok parameter (Toksin 50 keluhan, Tubuh, Tanda Vital, Profil Darah, dan Indeks GKI).", COLOR_NAVY),
        ("Increment 4 - Monitoring Perkembangan:", "Penyajian 6 grafik analitis untuk mengevaluasi penurunan toksin dan adaptasi ketosis terapeutik pasien.", COLOR_TEAL),
        ("Increment 5 - Aktivitas Mandiri MHLS:", "Otomatisasi pengingat puasa/gaya hidup via Durable Timer Temporal dan evaluasi kepatuhan harian pasien.", COLOR_NAVY),
    ]

    for idx, (inc_title, inc_desc, col) in enumerate(increments):
        t_pos = Inches(2.2) + idx * Inches(1.4)
        cd = add_card(s11, Inches(1.2), t_pos, Inches(17.6), Inches(1.2), COLOR_BG_WHITE if idx % 2 == 0 else COLOR_BG_CARD, COLOR_BORDER_TEAL)
        tf = cd.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.line_spacing = 1.15
        r = p.add_run()
        r.text = inc_title + " "
        r.font.name = FONT_HEAD
        r.font.size = Pt(15.5)
        r.font.bold = True
        r.font.color.rgb = col
        
        r2 = p.add_run()
        r2.text = inc_desc
        r2.font.name = FONT_MAIN
        r2.font.size = Pt(14)
        r2.font.color.rgb = COLOR_TEXT_MAIN

    # =========================================================================
    # SLIDE 12: OBJEK PENELITIAN & DATA (2 Cards)
    # =========================================================================
    s12 = prs.slides[11]
    clear_tf(s12.shapes[2].text_frame)
    clear_tf(s12.shapes[13].text_frame)
    set_slide_title(s12, "OBJEK PENELITIAN DAN PENGUMPULAN DATA")

    c12_1 = add_card(s12, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf12_1 = c12_1.text_frame
    tf12_1.word_wrap = True
    p = tf12_1.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Profil Objek Penelitian Klinik DSH\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf12_1, "Nama & Objek:", "Klinik Darus Syifa Holistik (DSH).", pt_size=14.5)
    add_point(tf12_1, "Wilayah Operasional:", "Serang, Provinsi Banten.", pt_size=14.5)
    add_point(tf12_1, "Pendekatan Pelayanan:", "Pelayanan kesehatan holistik integratif berbasis Moslem Healthy Lifestyle (MHLS).", pt_size=14.5)
    add_point(tf12_1, "Permasalahan Inti:", "Siklus panjang 12 pertemuan masih mengandalkan file Excel dan ketiadaan sistem pemantauan di luar klinik.", pt_size=14.5)

    c12_2 = add_card(s12, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf12_2 = c12_2.text_frame
    tf12_2.word_wrap = True
    p = tf12_2.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Metode Pengumpulan Data\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL
    add_point(tf12_2, "Observasi Lapangan:", "Mengamati langsung alur pemeriksaan fisik, evaluasi keluhan, dan interaksi pasien dengan terapis.", pt_size=14)
    add_point(tf12_2, "Wawancara Semi-Terstruktur:", "Wawancara dengan pimpinan klinik (Ust. Inayatullah, M.Pd) mengenai aturan 12 pertemuan MHLS.", pt_size=14)
    add_point(tf12_2, "Studi Dokumen & SOP Klinik:", "Menganalisis kuesioner 50 indikator toksin, rentang target IMT, serta formula resmi perhitungan GKI.", pt_size=14)
    add_point(tf12_2, "Studi Kepustakaan Ilmiah:", "Menelaah literatur jurnal internasional terkait Event-Driven Architecture, Outbox Pattern, dan Temporal.", pt_size=14)

    # =========================================================================
    # SLIDE 13: PROSES BISNIS BERJALAN (4 Sequential Step Cards)
    # =========================================================================
    s13 = prs.slides[12]
    clear_tf(s13.shapes[5].text_frame)
    set_slide_title(s13, "ANALISIS PROSES BISNIS YANG SEDANG BERJALAN")

    steps_curr = [
        ("Tahap 1: Pendaftaran Manual", "Pasien mendaftar secara tatap muka; petugas mencatat data ke buku tamu dan mengklasifikasikan jenis pasien (Umum/MHLS).", COLOR_NAVY),
        ("Tahap 2: Cek Fisik & Input Excel", "Pemeriksaan fisik dan uji lab sederhana dicatat petugas ke lembar kerja spreadsheet Excel secara manual.", COLOR_TEAL),
        ("Tahap 3: Pemberian Kertas Instruksi", "Petugas memberikan lembaran instruksi aktivitas mandiri untuk dijalankan pasien di rumah hingga kunjungan berikutnya.", COLOR_NAVY),
        ("Tahap 4: Evaluasi Tertunda di Klinik", "Kepatuhan pasien baru dinilai saat pasien datang kembali pada jadwal berikutnya melalui tanya-jawab retrospektif.", COLOR_TEAL),
    ]

    for idx, (st_t, st_d, col) in enumerate(steps_curr):
        t_pos = Inches(2.2) + idx * Inches(1.7)
        cd = add_card(s13, Inches(1.2), t_pos, Inches(17.6), Inches(1.5), COLOR_BG_WHITE if idx % 2 == 0 else COLOR_BG_CARD, COLOR_BORDER_NAVY)
        tf = cd.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        r = p.add_run()
        r.text = st_t + "\n"
        r.font.name = FONT_HEAD
        r.font.size = Pt(16)
        r.font.bold = True
        r.font.color.rgb = col
        
        p2 = tf.add_paragraph()
        p2.line_spacing = 1.15
        r2 = p2.add_run()
        r2.text = st_d
        r2.font.name = FONT_MAIN
        r2.font.size = Pt(14)
        r2.font.color.rgb = COLOR_TEXT_MAIN

    # =========================================================================
    # SLIDE 14: PROSES BISNIS DIUSULKAN (4 Sequential Step Cards)
    # =========================================================================
    s14 = prs.slides[13]
    clear_tf(s14.shapes[5].text_frame)
    set_slide_title(s14, "ANALISIS PROSES BISNIS YANG DIUSULKAN")

    steps_prop = [
        ("1. Registrasi Digital & Penerbitan Akun Mobile", "Petugas mendaftarkan pasien via web; peserta program MHLS otomatis mendapatkan akun untuk login di aplikasi mobile.", COLOR_TEAL),
        ("2. Booking Kunjungan Mandiri & Antrean Otomatis", "Pasien mengajukan jadwal booking via smartphone; jadwal terkonfirmasi otomatis muncul sebagai antrean di modul pemeriksaan klinik.", COLOR_NAVY),
        ("3. Rekam Terstruktur 5 Kelompok Klinis & Event Outbox", "Hasil pemeriksaan fisik & lab diinput secara terstruktur; sistem menerbitkan domain event secara asinkron bebas risiko inkonsistensi.", COLOR_TEAL),
        ("4. Orkestrasi Temporal & Pelaporan Berkala", "Durable Workflow menjalankan timer pengingat otomatis; pasien melaporkan kepatuhan dari rumah dan grafik klinis terbarui seketika.", COLOR_NAVY),
    ]

    for idx, (st_t, st_d, col) in enumerate(steps_prop):
        t_pos = Inches(2.2) + idx * Inches(1.7)
        cd = add_card(s14, Inches(1.2), t_pos, Inches(17.6), Inches(1.5), COLOR_BG_CARD if idx % 2 == 0 else COLOR_BG_WHITE, COLOR_BORDER_TEAL)
        tf = cd.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        r = p.add_run()
        r.text = st_t + "\n"
        r.font.name = FONT_HEAD
        r.font.size = Pt(16)
        r.font.bold = True
        r.font.color.rgb = col
        
        p2 = tf.add_paragraph()
        p2.line_spacing = 1.15
        r2 = p2.add_run()
        r2.text = st_d
        r2.font.name = FONT_MAIN
        r2.font.size = Pt(14)
        r2.font.color.rgb = COLOR_TEXT_MAIN

    # =========================================================================
    # SLIDE 15: ARSITEKTUR SISTEM (4 Layer Cards)
    # =========================================================================
    s15 = prs.slides[14]
    clear_tf(s15.shapes[4].text_frame)
    clear_tf(s15.shapes[6].text_frame)
    set_slide_title(s15, "RANCANGAN ARSITEKTUR SISTEM: EDA & TEMPORAL")

    layers = [
        ("Lapisan Klien (Client Layer)", "Web Admin Klinik (Blade/Tailwind/Chart.js) untuk operasional medis dan Mobile App Pasien (Flutter) untuk pelaporan mandiri.", COLOR_NAVY),
        ("Lapisan Backend & Basis Data", "Laravel REST API menangani logika bisnis, autentikasi Sanctum, dan penyimpanan relasional MySQL 8.0.", COLOR_TEAL),
        ("Lapisan Komunikasi Asinkron (EDA)", "Pola Transactional Outbox menjamin domain event tersimpan bersama transaksi bisnis dan disalurkan oleh Outbox Dispatcher.", COLOR_NAVY),
        ("Lapisan Orkestrasi Workflow (Temporal)", "Temporal Server & Worker mengelola eksekusi alur kerja jangka panjang, durable timer, dan penanganan sinyal laporan pasien.", COLOR_TEAL),
    ]

    for idx, (l_t, l_d, col) in enumerate(layers):
        t_pos = Inches(2.2) + idx * Inches(1.7)
        cd = add_card(s15, Inches(1.2), t_pos, Inches(17.6), Inches(1.5), COLOR_BG_WHITE if idx % 2 == 0 else COLOR_BG_CARD, COLOR_BORDER_NAVY)
        tf = cd.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        r = p.add_run()
        r.text = l_t + "\n"
        r.font.name = FONT_HEAD
        r.font.size = Pt(16)
        r.font.bold = True
        r.font.color.rgb = col
        
        p2 = tf.add_paragraph()
        p2.line_spacing = 1.15
        r2 = p2.add_run()
        r2.text = l_d
        r2.font.name = FONT_MAIN
        r2.font.size = Pt(14)
        r2.font.color.rgb = COLOR_TEXT_MAIN

    # =========================================================================
    # SLIDE 16: MODUL & BASIS DATA (2 Cards)
    # =========================================================================
    s16 = prs.slides[15]
    clear_tf(s16.shapes[4].text_frame)
    clear_tf(s16.shapes[6].text_frame)
    set_slide_title(s16, "RANCANGAN 5 MODUL SISTEM & BASIS DATA")

    c16_1 = add_card(s16, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf16_1 = c16_1.text_frame
    tf16_1.word_wrap = True
    p = tf16_1.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "5 Modul Fungsional Layanan Klinik\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf16_1, "1. Modul Data Pasien:", "Pengelolaan profil, No RM otomatis, & klasifikasi pasien Umum vs Program MHLS.", pt_size=13.5)
    add_point(tf16_1, "2. Modul Jadwal & Kunjungan:", "Sinkronisasi antrean klinik dengan pengajuan booking 12 pertemuan dari aplikasi mobile.", pt_size=13.5)
    add_point(tf16_1, "3. Modul Pemeriksaan Klinis:", "Pencatatan 5 kelompok parameter (Toksin 50 indikator, Tubuh, Vital, Darah, & GKI).", pt_size=13.5)
    add_point(tf16_1, "4. Modul Monitoring Perkembangan:", "Visualisasi 6 grafik analitis untuk mengevaluasi progres penurunan toksin & ketosis.", pt_size=13.5)
    add_point(tf16_1, "5. Modul Aktivitas Mandiri MHLS:", "Pengelolaan instruksi gaya hidup, jadwal pengingat, dan orkestrasi workflow Temporal.", pt_size=13.5)

    c16_2 = add_card(s16, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf16_2 = c16_2.text_frame
    tf16_2.word_wrap = True
    p = tf16_2.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Entitas Basis Data Utama (MySQL 8.0)\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL
    add_point(tf16_2, "users & patients:", "Kredensial autentikasi Sanctum dan data demografi pasien.", pt_size=13.5)
    add_point(tf16_2, "visits:", "Tanggal, slot waktu, nomor pertemuan (1-12), dan status booking kunjungan.", pt_size=13.5)
    add_point(tf16_2, "patient_progress_sessions:", "Menghubungkan kunjungan dengan rekam evaluasi klinis dan catatan staff.", pt_size=13.5)
    add_point(tf16_2, "toxin, body, & blood_measurements:", "Detail 50 keluhan toksin, komposisi berat/IMT, tekanan darah, glukosa, keton, & GKI.", pt_size=13.5)
    add_point(tf16_2, "activity_plans & schedules:", "Instruksi hidup sehat, periode waktu, frekuensi, dan tracking laporan pasien.", pt_size=13.5)
    add_point(tf16_2, "outbox_events & workflow_references:", "Pencatatan transactional outbox event dan mapping ID workflow Temporal.", pt_size=13.5)

    # =========================================================================
    # SLIDE 17: IMPLEMENTASI & PENGUJIAN (2 Cards)
    # =========================================================================
    s17 = prs.slides[16]
    clear_tf(s17.shapes[5].text_frame)
    set_slide_title(s17, "RANCANGAN IMPLEMENTASI DAN METODE PENGUJIAN")

    c17_1 = add_card(s17, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf17_1 = c17_1.text_frame
    tf17_1.word_wrap = True
    p = tf17_1.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Lingkungan Implementasi Sistem\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf17_1, "Kontainerisasi Docker Compose:", "Layanan backend API, MySQL 8.0, Temporal Server, Temporal UI, Worker Go, dan Outbox Dispatcher berjalan terisolasi.", pt_size=14)
    add_point(tf17_1, "Backend REST API (Laravel):", "Melayani autentikasi token Sanctum, validasi transaksi, dan integrasi antar-modul klinik.", pt_size=14)
    add_point(tf17_1, "Antarmuka Modern Medis:", "Mengusung palet warna resmi Klinik DSH (#20596c), layout Filament yang bersih, dan visualisasi interaktif Chart.js.", pt_size=14)

    c17_2 = add_card(s17, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf17_2 = c17_2.text_frame
    tf17_2.word_wrap = True
    p = tf17_2.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Skenario Pengujian Sistem\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL
    add_point(tf17_2, "Black Box Testing (Fungsional):", "Memverifikasi seluruh fungsionalitas registrasi pasien, booking jadwal, input 5 kelompok klinis, dan pelaporan mobile.", pt_size=14)
    add_point(tf17_2, "Pengujian Alur Event EDA:", "Memastikan domain event tersimpan dalam database transaction yang sama dan terkirim tanpa dual-write failure.", pt_size=14)
    add_point(tf17_2, "Pengujian Ketahanan Worker Temporal:", "Menguji penghentian paksa worker saat workflow aktif; membuktikan pemulihan timer dan kelanjutan proses otomatis.", pt_size=14)

    # =========================================================================
    # SLIDE 18: HASIL IMPLEMENTASI & PEMBAHASAN (2 Cards)
    # =========================================================================
    s18 = prs.slides[17]
    clear_tf(s18.shapes[2].text_frame)
    clear_tf(s18.shapes[13].text_frame)
    clear_tf(s18.shapes[14].text_frame)
    set_slide_title(s18, "HASIL IMPLEMENTASI DAN PEMBAHASAN")

    c18_1 = add_card(s18, Inches(1.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf18_1 = c18_1.text_frame
    tf18_1.word_wrap = True
    p = tf18_1.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Hasil Implementasi Sistem Nyata\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf18_1, "Dashboard Bersih & Klinis:", "Menyajikan ringkasan operasional pasien, grafik tren harian, antrean periksa, dan riwayat klinis tanpa polusi data teknis.", pt_size=14)
    add_point(tf18_1, "Pemeriksaan 5 Kelompok Parameter:", "Pencatatan 50 indikator toksin, tubuh, tekanan darah, metabolik, dan kalkulasi otomatis indeks GKI berfungsi presisi.", pt_size=14)
    add_point(tf18_1, "6 Visualisasi Grafik Analitis:", "Kurva analitis berhasil memetakan penurunan toksin (18 ke 3) dan pencapaian zona ketosis terapeutik pasien MHLS.", pt_size=14)
    add_point(tf18_1, "Integrasi Mobile App Pasien:", "Pasien dapat melihat jadwal terkonfirmasi dan melaporkan checklist aktivitas mandiri harian secara real-time.", pt_size=14)

    c18_2 = add_card(s18, Inches(10.2), Inches(2.2), Inches(8.6), Inches(7.0), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf18_2 = c18_2.text_frame
    tf18_2.word_wrap = True
    p = tf18_2.paragraphs[0]
    p.space_after = Pt(12)
    r = p.add_run()
    r.text = "Pembahasan Keandalan EDA & Temporal\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(18)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL
    add_point(tf18_2, "Integritas Event EDA:", "Pola Transactional Outbox terbukti mengeliminasi risiko dual-write failure antara database MySQL dan broker event.", pt_size=14)
    add_point(tf18_2, "Orkestrasi Jangka Panjang:", "Temporal berhasil mengelola durasi tunggu program 12 sesi menggunakan Durable Timer yang efisien sumber daya.", pt_size=14)
    add_point(tf18_2, "Ketahanan Failover (Resilience):", "Uji terminasi worker membuktikan workflow tetap persisten di server dan langsung dilanjutkan saat worker hidup kembali.", pt_size=14)
    add_point(tf18_2, "Otomasi Penautan Jadwal:", "Jadwal booking kunjungan dari pasien langsung tertaut otomatis sebagai antrean 'Belum Diisi' pada modul pemeriksaan klinik.", pt_size=14)

    # =========================================================================
    # SLIDE 19: KESIMPULAN, SARAN & DAFTAR PUSTAKA (3 Structured Cards)
    # =========================================================================
    s19 = prs.slides[18]
    clear_tf(s19.shapes[5].text_frame)
    clear_tf(s19.shapes[7].text_frame)
    set_slide_title(s19, "KESIMPULAN, SARAN DAN DAFTAR PUSTAKA")

    # Card Kiri: Kesimpulan
    c19_k = add_card(s19, Inches(1.2), Inches(2.2), Inches(8.6), Inches(4.5), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf19_k = c19_k.text_frame
    tf19_k.word_wrap = True
    p = tf19_k.paragraphs[0]
    p.space_after = Pt(8)
    r = p.add_run()
    r.text = "Kesimpulan Penelitian\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(17)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY
    add_point(tf19_k, "K-1 (Penyelesaian Masalah):", "Sistem berhasil menggantikan pencatatan spreadsheet manual menjadi platform digital terpadu dengan 5 modul klinis.", pt_size=13)
    add_point(tf19_k, "K-2 (Efektivitas EDA):", "Penerapan EDA via Transactional Outbox menjamin konsistensi data transaksi dan mempermudah integrasi asinkron antarbagian.", pt_size=13)
    add_point(tf19_k, "K-3 (Keandalan Temporal):", "Temporal terbukti andal mengelola alur program MHLS jangka panjang dan tahan terhadap gangguan worker tanpa kehilangan state.", pt_size=13)

    # Card Kanan: Saran
    c19_s = add_card(s19, Inches(10.2), Inches(2.2), Inches(8.6), Inches(4.5), COLOR_BG_CARD, COLOR_BORDER_TEAL)
    tf19_s = c19_s.text_frame
    tf19_s.word_wrap = True
    p = tf19_s.paragraphs[0]
    p.space_after = Pt(8)
    r = p.add_run()
    r.text = "Saran Pengembangan Lanjutan\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(17)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL
    add_point(tf19_s, "S-1 (Integrasi Perangkat IoT):", "Integrasi langsung dengan alat timbang bioimpedansi dan glucometer Bluetooth untuk input otomatis tanpa ketik manual.", pt_size=13)
    add_point(tf19_s, "S-2 (Notifikasi Multikanal):", "Penambahan channel notifikasi pengingat via WhatsApp Gateway selain push notification aplikasi mobile.", pt_size=13)
    add_point(tf19_s, "S-3 (Skalabilitas Multi-Klinik):", "Pengembangan arsitektur multi-tenant untuk mendukung operasional cabang klinik holistik di berbagai kota.", pt_size=13)

    # Card Bawah: Daftar Pustaka
    c19_p = add_card(s19, Inches(1.2), Inches(6.9), Inches(17.6), Inches(2.3), COLOR_BG_WHITE, COLOR_BORDER_NAVY)
    tf19_p = c19_p.text_frame
    tf19_p.word_wrap = True
    p = tf19_p.paragraphs[0]
    p.space_after = Pt(4)
    r = p.add_run()
    r.text = "Daftar Pustaka Utama (Format APA 7):\n"
    r.font.name = FONT_HEAD
    r.font.size = Pt(13.5)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY

    pustaka = [
        "• Nagpal, V. (2026). Comparative Analysis of Workflow Orchestration Frameworks for Resilient Distributed Systems. Journal of Software Engineering, 18(2), 112-125.",
        "• Nowell, W. B., et al. (2023). Patient Adherence and Longitudinal Monitoring in Digital Health Applications. BMC Medical Informatics, 23(1), 45-58.",
        "• Syarif, M., & Pizaini. (2022). Penerapan Event-Driven Architecture pada Sistem Informasi Berbasis Microservices. Jurnal Rekayasa Sistem, 8(3), 201-210.",
        "• Yasini, M., et al. (2025). Identification and Categorization of Clinical Events in Healthcare Digitalization. IEEE Access, 13, 34210-34224."
    ]
    for pus in pustaka:
        p = tf19_p.add_paragraph()
        p.space_after = Pt(2)
        r = p.add_run()
        r.text = pus
        r.font.name = FONT_MAIN
        r.font.size = Pt(10.5)
        r.font.color.rgb = COLOR_TEXT_MUTED

    # =========================================================================
    # SLIDE 20: TERIMA KASIH (Tetap Pertahankan Elemen Asli Template)
    # =========================================================================
    s20 = prs.slides[19]

    prs.save(output_path)
    print(f"Presentation successfully enhanced & saved to {output_path} with {len(prs.slides)} slides!")

if __name__ == '__main__':
    create_presentation()
