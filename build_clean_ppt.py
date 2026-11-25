import sys
import os
import copy
import pptx
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.enum.shapes import MSO_SHAPE
from pptx.opc.package import _Relationship

def build_presentation():
    template_path = 'TEMPLATE PPT TUGAS AKHIR - FASILKOM.pptx'
    output_path = 'Presentasi_TA_DSH_20_Slides.pptx'

    prs = Presentation(template_path)
    blank_layout = prs.slide_layouts[0]

    orig_slides = list(prs.slides)
    num_orig = len(orig_slides)

    # Pemetaan 20 slide menggunakan layout template resmi Fasilkom:
    # 0: Cover (T1)
    # 1: Template Slide 2 (Header Ribbon + Corner Dots + Footer)
    # 2: Template Slide 3 (Header Ribbon + Corner Curves + Footer)
    # 4: Template Slide 5 (Header Ribbon + Corner Shapes + Footer)
    # 5: Template Slide 6 (Header Ribbon + Corner Shapes + Footer)
    # 7: Template Slide 8 (Header Ribbon + Wave + Footer)
    # 10: Template Slide 11 (Header Ribbon + Footer)
    # 11: Template Slide 12 (Terima Kasih)
    slide_sources = [
        0,  # Slide 1  (Cover T1)
        1,  # Slide 2  (Latar Belakang T2)
        1,  # Slide 3  (Masalah & Tujuan T2)
        1,  # Slide 4  (Batasan & Manfaat T2)
        2,  # Slide 5  (Studi Literatur T3)
        2,  # Slide 6  (Research Gap & Novelty T3)
        4,  # Slide 7  (Teori: Monitoring & EDA T5)
        4,  # Slide 8  (Teori: Temporal T5)
        4,  # Slide 9  (Teori: Stack & Metode T5)
        4,  # Slide 10 (DSR 6 Tahap T5)
        5,  # Slide 11 (Tahapan & 5 Increment T6)
        4,  # Slide 12 (Objek & Data T5)
        7,  # Slide 13 (Proses Berjalan T8)
        7,  # Slide 14 (Proses Diusulkan T8)
        7,  # Slide 15 (Arsitektur Sistem T8)
        7,  # Slide 16 (Modul & Skema Data T8)
        7,  # Slide 17 (Implementasi & Pengujian T8)
        7,  # Slide 18 (Hasil & Pembahasan T8)
        10, # Slide 19 (Kesimpulan & Pustaka T11)
        11  # Slide 20 (Terima Kasih T12)
    ]

    new_slides = []
    for src_idx in slide_sources:
        src_slide = orig_slides[src_idx]
        new_slide = prs.slides.add_slide(blank_layout)
        
        # 1. Salin seluruh shape dari slide sumber
        for shape in src_slide.shapes:
            el = copy.deepcopy(shape.element)
            new_slide.shapes._spTree.append(el)
            
        # 2. Salin seluruh relationships (gambar logo Esa Unggul, dekorasi, dll) dengan rId yang sama persis
        for rel in src_slide.part.rels.values():
            if rel.rId not in new_slide.part.rels:
                target = rel.target_ref if rel.is_external else rel.target_part
                new_rel = _Relationship(new_slide.part.rels._base_uri, rel.rId, rel.reltype, rel._target_mode, target)
                new_slide.part.rels._rels[rel.rId] = new_rel
                
        new_slides.append(new_slide)

    # Hapus 12 slide asli setelah semua slide baru terbuat
    for _ in range(num_orig):
        rId = prs.slides._sldIdLst[0].rId
        prs.part.drop_rel(rId)
        del prs.slides._sldIdLst[0]

    # Palet Warna Visual Profesional & Medis Modern
    COLOR_NAVY = RGBColor(0x28, 0x29, 0x5D)        # Navy Template Fasilkom (#28295D)
    COLOR_TEAL = RGBColor(0x20, 0x59, 0x6C)        # Teal Resmi Klinik DSH (#20596C)
    COLOR_TEAL_DARK = RGBColor(0x13, 0x3E, 0x4B)   # Dark Teal
    COLOR_WHITE = RGBColor(0xFF, 0xFF, 0xFF)       # Putih Bersih
    COLOR_CARD_BG = RGBColor(0xFF, 0xFF, 0xFF)     # Putih untuk Card
    COLOR_CARD_ALT = RGBColor(0xF8, 0xFA, 0xFC)    # Slate 50 Soft Tint
    COLOR_BORDER = RGBColor(0xCB, 0xD5, 0xE1)      # Slate 300 Border Lembut
    COLOR_BORDER_TEAL = RGBColor(0x20, 0x59, 0x6C) # Border Teal
    COLOR_TEXT_MAIN = RGBColor(0x1E, 0x29, 0x3B)   # Slate 800 Sangat Terbaca
    COLOR_TEXT_MUTED = RGBColor(0x47, 0x55, 0x69)  # Slate 600
    COLOR_AMBER = RGBColor(0xD9, 0x77, 0x06)       # Amber 600
    COLOR_GREEN = RGBColor(0x05, 0x96, 0x69)       # Emerald 600
    COLOR_SOFT_TEAL = RGBColor(0xF0, 0xFD, 0xFA)    # Mint Soft
    COLOR_SOFT_RED = RGBColor(0xFE, 0xF2, 0xF2)     # Red Soft

    FONT_HEAD = 'Poppins'
    FONT_BODY = 'Plus Jakarta Sans'

    slide_metadata = {
        1:  ("LATAR BELAKANG PENELITIAN", "Latar Belakang Penelitian"),
        2:  ("RUMUSAN MASALAH & TUJUAN", "Rumusan Masalah dan Tujuan"),
        3:  ("BATASAN & MANFAAT PENELITIAN", "Batasan dan Manfaat Penelitian"),
        4:  ("STUDI LITERATUR", "Studi Literatur"),
        5:  ("RESEARCH GAP & NOVELTY", "Research Gap dan Kebaruan Penelitian"),
        6:  ("LANDASAN TEORI: MONITORING & EDA", "Landasan Teori: Monitoring dan EDA"),
        7:  ("LANDASAN TEORI: TEMPORAL", "Landasan Teori: Temporal"),
        8:  ("LANDASAN TEORI: STACK & METODE", "Landasan Teori: Teknologi dan Metode"),
        9:  ("METODOLOGI PENELITIAN: 6 TAHAP DSR", "Metodologi Penelitian: 6 Tahapan DSR"),
        10: ("TAHAPAN PENELITIAN & 5 INCREMENT", "Tahapan Penelitian dan 5 Increment"),
        11: ("OBJEK PENELITIAN & DATA", "Profil Objek Penelitian dan Data"),
        12: ("ANALISIS PROSES BISNIS BERJALAN", "Analisis Proses Bisnis yang Berjalan"),
        13: ("ANALISIS PROSES BISNIS DIUSULKAN", "Analisis Proses Bisnis yang Diusulkan"),
        14: ("RANCANGAN ARSITEKTUR SISTEM", "Rancangan Arsitektur Sistem"),
        15: ("RANCANGAN 5 MODUL & BASIS DATA", "Rancangan 5 Modul dan Basis Data"),
        16: ("RANCANGAN IMPLEMENTASI & PENGUJIAN", "Rancangan Implementasi dan Pengujian"),
        17: ("HASIL IMPLEMENTASI & PEMBAHASAN", "Hasil Implementasi dan Pembahasan"),
        18: ("KESIMPULAN, SARAN & DAFTAR PUSTAKA", "Kesimpulan, Saran dan Daftar Pustaka"),
    }

    # Helper: Menyesuaikan Header Ribbon & Footer Bawaan Template
    def customize_template_chrome(slide, s_idx):
        h_title, f_title = slide_metadata[s_idx]

        # 1. Sesuaikan Header Text Box bawaan template
        h_shape = None
        for sh in slide.shapes:
            if sh.has_text_frame and (sh.top < 2.0 * 914400 and sh.top > 0.7 * 914400):
                if sh.left > 4.5 * 914400 and sh.left < 7.5 * 914400:
                    h_shape = sh
                    break

        if h_shape:
            tf = h_shape.text_frame
            tf.word_wrap = True
            for p in tf.paragraphs:
                p.text = ""
            p = tf.paragraphs[0]
            p.alignment = PP_ALIGN.CENTER
            r = p.add_run()
            r.text = h_title
            r.font.name = FONT_HEAD
            if len(h_title) > 30:
                r.font.size = Pt(17)
            elif len(h_title) > 24:
                r.font.size = Pt(18.5)
            else:
                r.font.size = Pt(21)
            r.font.bold = True
            r.font.color.rgb = COLOR_WHITE

        # 2. Sesuaikan Footer Text bawaan template
        for sh in slide.shapes:
            if sh.top >= 10.0 * 914400:
                if sh.shape_type == 6: # GROUP
                    for child in sh.shapes:
                        if child.has_text_frame and child.text_frame.text.strip():
                            tf = child.text_frame
                            for p in tf.paragraphs:
                                p.text = ""
                            p = tf.paragraphs[0]
                            p.alignment = PP_ALIGN.CENTER
                            r = p.add_run()
                            r.text = f_title
                            r.font.name = FONT_BODY
                            r.font.size = Pt(11)
                            r.font.color.rgb = COLOR_WHITE
                elif sh.has_text_frame and sh.text_frame.text.strip():
                    tf = sh.text_frame
                    for p in tf.paragraphs:
                        p.text = ""
                    p = tf.paragraphs[0]
                    p.alignment = PP_ALIGN.CENTER
                    r = p.add_run()
                    r.text = f_title
                    r.font.name = FONT_BODY
                    r.font.size = Pt(11)
                    r.font.color.rgb = COLOR_WHITE

        # 3. Hapus HANYA dummy body placeholder bawaan (seperti Research Question pada Slide 2)
        to_del = []
        for sh in slide.shapes:
            if sh.name in ["Google Shape;113;p2", "Google Shape;116;p2", "Google Shape;119;p2", "Google Shape;120;p2"]:
                to_del.append(sh)
        for sh in to_del:
            el = sh.element
            el.getparent().remove(el)

    for idx in range(1, 19):
        customize_template_chrome(new_slides[idx], idx)

    # Helper: Membuat Visual Card Container
    def create_card(slide, left, top, width, height, bg_color=COLOR_WHITE, border_color=COLOR_BORDER, border_width=1.5):
        card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        card.fill.solid()
        card.fill.fore_color.rgb = bg_color
        card.line.color.rgb = border_color
        card.line.width = Pt(border_width)
        return card

    # Helper: Membuat Card Header Strip
    def add_card_header(slide, left, top, width, height, title, badge_text="", header_bg=COLOR_NAVY, text_color=COLOR_WHITE):
        hdr = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        hdr.fill.solid()
        hdr.fill.fore_color.rgb = header_bg
        hdr.line.color.rgb = header_bg
        tf = hdr.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.alignment = PP_ALIGN.LEFT
        p.line_spacing = 1.0

        if badge_text:
            r_b = p.add_run()
            r_b.text = f" {badge_text}  "
            r_b.font.name = FONT_HEAD
            r_b.font.size = Pt(12.5)
            r_b.font.bold = True
            r_b.font.color.rgb = COLOR_WHITE

        r = p.add_run()
        r.text = title
        r.font.name = FONT_HEAD
        r.font.size = Pt(13.5)
        r.font.bold = True
        r.font.color.rgb = text_color
        return hdr

    # Helper: Menambahkan Bullet Item Berstruktur
    def add_bullet_item(tf, title, desc="", marker="▪ ", pt_size=13.5, marker_color=COLOR_TEAL, title_color=COLOR_NAVY):
        p = tf.add_paragraph() if tf.paragraphs[0].text else tf.paragraphs[0]
        p.space_after = Pt(8)
        p.line_spacing = 1.25

        r_m = p.add_run()
        r_m.text = marker
        r_m.font.name = FONT_BODY
        r_m.font.size = Pt(pt_size)
        r_m.font.bold = True
        r_m.font.color.rgb = marker_color

        r_t = p.add_run()
        r_t.text = title + (" " if desc else "")
        r_t.font.name = FONT_BODY
        r_t.font.size = Pt(pt_size)
        r_t.font.bold = True
        r_t.font.color.rgb = title_color

        if desc:
            r_d = p.add_run()
            r_d.text = desc
            r_d.font.name = FONT_BODY
            r_d.font.size = Pt(pt_size)
            r_d.font.bold = False
            r_d.font.color.rgb = COLOR_TEXT_MAIN

    # Helper: Membuat KPI Stat Box
    def add_kpi_box(slide, left, top, width, height, number_text, label_text, color=COLOR_TEAL):
        box = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        box.fill.solid()
        box.fill.fore_color.rgb = COLOR_WHITE
        box.line.color.rgb = color
        box.line.width = Pt(1.8)
        tf = box.text_frame
        tf.word_wrap = True

        p1 = tf.paragraphs[0]
        p1.alignment = PP_ALIGN.CENTER
        r1 = p1.add_run()
        r1.text = number_text + "\n"
        r1.font.name = FONT_HEAD
        r1.font.size = Pt(21)
        r1.font.bold = True
        r1.font.color.rgb = color

        p2 = tf.add_paragraph()
        p2.alignment = PP_ALIGN.CENTER
        r2 = p2.add_run()
        r2.text = label_text
        r2.font.name = FONT_BODY
        r2.font.size = Pt(11)
        r2.font.bold = True
        r2.font.color.rgb = COLOR_TEXT_MUTED
        return box

    # Helper: Membuat Callout Footer Strip di dalam Card
    def add_card_footer_badge(slide, left, top, width, height, text, bg_color=COLOR_SOFT_TEAL, border_color=COLOR_BORDER_TEAL, text_color=COLOR_TEAL):
        badge = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        badge.fill.solid()
        badge.fill.fore_color.rgb = bg_color
        badge.line.color.rgb = border_color
        badge.line.width = Pt(1.2)
        tf = badge.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.alignment = PP_ALIGN.CENTER
        r = p.add_run()
        r.text = text
        r.font.name = FONT_BODY
        r.font.size = Pt(11.5)
        r.font.bold = True
        r.font.color.rgb = text_color
        return badge

    CONTENT_TOP = Inches(2.45)
    CONTENT_H = Inches(7.35)

    # =========================================================================
    # SLIDE 1: COVER
    # =========================================================================
    s1 = new_slides[0]
    s1_title = s1.shapes[3]
    s1_title.text_frame.word_wrap = True
    for p in s1_title.text_frame.paragraphs:
        p.text = ""
    p0 = s1_title.text_frame.paragraphs[0]
    p0.line_spacing = 1.15
    p0.alignment = PP_ALIGN.LEFT
    r = p0.add_run()
    r.text = "IMPLEMENTASI EVENT-DRIVEN ARCHITECTURE DENGAN DURABLE WORKFLOW ORCHESTRATION MENGGUNAKAN TEMPORAL PADA SISTEM MONITORING PERKEMBANGAN PASIEN"
    r.font.name = FONT_HEAD
    r.font.size = Pt(21)
    r.font.bold = True
    r.font.color.rgb = COLOR_NAVY

    s1_dosen = s1.shapes[7]
    for p in s1_dosen.text_frame.paragraphs:
        p.text = ""
    p = s1_dosen.text_frame.paragraphs[0]
    r = p.add_run()
    r.text = "Dr. Riya Widayanti, S.Kom., M.M.S.I."
    r.font.name = FONT_HEAD
    r.font.size = Pt(15.5)
    r.font.bold = True
    r.font.color.rgb = COLOR_TEAL

    s1_mhs = s1.shapes[8]
    for p in s1_mhs.text_frame.paragraphs:
        p.text = ""
    p1 = s1_mhs.text_frame.paragraphs[0]
    r1 = p1.add_run()
    r1.text = "MUHAMAD ROHISUL IMAN\n"
    r1.font.name = FONT_HEAD
    r1.font.size = Pt(17.5)
    r1.font.bold = True
    r1.font.color.rgb = COLOR_NAVY

    r2 = p1.add_run()
    r2.text = "NIM : 20230801270\n"
    r2.font.name = FONT_BODY
    r2.font.size = Pt(15)
    r2.font.bold = True
    r2.font.color.rgb = COLOR_TEAL

    r3 = p1.add_run()
    r3.text = "Program Studi Teknik Informatika\nFakultas Ilmu Komputer, Universitas Esa Unggul\nObjek Penelitian: Klinik Darus Syifa Holistik (DSH)\nTahun: 2026-2027"
    r3.font.name = FONT_BODY
    r3.font.size = Pt(13)
    r3.font.color.rgb = COLOR_TEXT_MUTED

    # =========================================================================
    # SLIDE 2: LATAR BELAKANG PENELITIAN (3 Visual Cards + Status Badges)
    # =========================================================================
    s2 = new_slides[1]
    c1 = create_card(s2, Inches(1.2), CONTENT_TOP, Inches(5.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s2, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(5.2), Inches(0.55), "Kondisi Eksisting Klinik DSH", "01", COLOR_NAVY)
    tb1 = s2.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(5.2), Inches(5.4))
    tf1 = tb1.text_frame
    tf1.word_wrap = True
    add_bullet_item(tf1, "Pencatatan Masih Manual:", "Seluruh rekam data pemeriksaan, evaluasi, dan kunjungan pasien masih dicatat di file Excel terpisah.", pt_size=13.5)
    add_bullet_item(tf1, "Dua Kategori Pasien:", "Klinik melayani Pasien Umum (kunjungan bebas) dan Pasien Program MHLS (program terstruktur 12 pertemuan).", pt_size=13.5)
    add_bullet_item(tf1, "Keterbatasan Spreadsheet:", "Sulit mendeteksi tren longitudinal dan rentan inkonsistensi riwayat rekam medis pasien.", pt_size=13.5)
    add_card_footer_badge(s2, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(5.2), Inches(0.55), "Status: Rekam Medis Belum Terintegrasi", COLOR_SOFT_RED, RGBColor(0xEF, 0x44, 0x44), RGBColor(0x99, 0x1B, 0x1B))

    c2 = create_card(s2, Inches(7.2), CONTENT_TOP, Inches(5.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s2, Inches(7.4), CONTENT_TOP + Inches(0.2), Inches(5.2), Inches(0.55), "Karakteristik Program MHLS", "02", COLOR_TEAL)
    tb2 = s2.shapes.add_textbox(Inches(7.4), CONTENT_TOP + Inches(0.9), Inches(5.2), Inches(5.4))
    tf2 = tb2.text_frame
    tf2.word_wrap = True
    add_bullet_item(tf2, "Siklus Panjang 3 Bulan:", "Pasien menjalani 12 kali pertemuan klinis terjadwal dengan interval waktu beberapa hari hingga minggu.", pt_size=13.5)
    add_bullet_item(tf2, "Aktivitas Mandiri di Rumah:", "Di antara jadwal pertemuan, pasien wajib menjalankan pola puasa, real food, dan istirahat teratur.", pt_size=13.5)
    add_bullet_item(tf2, "Evaluasi Tertunda:", "Saat ini evaluasi kepatuhan baru bisa dilakukan secara kuis tanya-jawab saat pasien datang kembali ke klinik.", pt_size=13.5)
    add_card_footer_badge(s2, Inches(7.4), CONTENT_TOP + Inches(6.55), Inches(5.2), Inches(0.55), "Karakteristik: Alur Kerja Jangka Panjang (Long-Running)", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    c3 = create_card(s2, Inches(13.2), CONTENT_TOP, Inches(5.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s2, Inches(13.4), CONTENT_TOP + Inches(0.2), Inches(5.2), Inches(0.55), "Solusi EDA & Temporal", "03", COLOR_NAVY)
    tb3 = s2.shapes.add_textbox(Inches(13.4), CONTENT_TOP + Inches(0.9), Inches(5.2), Inches(5.4))
    tf3 = tb3.text_frame
    tf3.word_wrap = True
    add_bullet_item(tf3, "Aplikasi Web & Mobile:", "Web Admin untuk tata kelola klinik dan Mobile App bagi pasien untuk pelaporan mandiri secara berkala.", pt_size=13.5)
    add_bullet_item(tf3, "Event-Driven Architecture:", "Menghubungkan proses pendaftaran, jadwal, dan rekam klinis secara asinkron bebas ketergantungan erat.", pt_size=13.5)
    add_bullet_item(tf3, "Durable Orchestrator:", "Temporal mengelola timer pengingat otomatis dan menjamin alur pemantauan tetap berjalan tahan kegagalan.", pt_size=13.5)
    add_card_footer_badge(s2, Inches(13.4), CONTENT_TOP + Inches(6.55), Inches(5.2), Inches(0.55), "Target Solusi: Zero Dual-Write & Otomasi Durable", RGBColor(0xF0, 0xFD, 0xF4), RGBColor(0x10, 0xB9, 0x81), RGBColor(0x06, 0x5F, 0x46))

    # =========================================================================
    # SLIDE 3: MASALAH, RUMUSAN & TUJUAN PENELITIAN (2 Columns)
    # =========================================================================
    s3 = new_slides[2]
    c_rm = create_card(s3, Inches(1.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s3, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Rumusan Masalah (RM)", "RM", COLOR_NAVY)
    tb_rm = s3.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf_rm = tb_rm.text_frame
    tf_rm.word_wrap = True
    add_bullet_item(tf_rm, "RM-1 (Rancang Bangun Sistem):", "Bagaimana merancang dan membangun sistem monitoring perkembangan pasien berbasis web dan mobile sesuai kebutuhan Klinik DSH?", marker="❶ ", pt_size=13.5)
    add_bullet_item(tf_rm, "RM-2 (Penerapan Komunikasi EDA):", "Bagaimana menerapkan Event-Driven Architecture untuk menghubungkan dan mengintegrasikan proses antarbagian sistem melalui pertukaran event?", marker="❷ ", pt_size=13.5)
    add_bullet_item(tf_rm, "RM-3 (Orkestrasi Workflow Temporal):", "Bagaimana menerapkan Durable Workflow Orchestration menggunakan Temporal untuk mengelola alur monitoring pasien MHLS yang berlangsung dalam jangka waktu tertentu?", marker="❸ ", pt_size=13.5)
    add_bullet_item(tf_rm, "RM-4 (Pengujian & Ketahanan Sistem):", "Bagaimana hasil pengujian fungsionalitas dan keandalan sistem dalam memproses event serta mempertahankan workflow setelah terjadi gangguan worker?", marker="❹ ", pt_size=13.5)
    add_card_footer_badge(s3, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Fokus Evaluasi: Fungsionalitas Modul & Ketahanan Failover", COLOR_CARD_ALT, COLOR_BORDER, COLOR_NAVY)

    c_tj = create_card(s3, Inches(10.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s3, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Tujuan Penelitian (T)", "T", COLOR_TEAL)
    tb_tj = s3.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf_tj = tb_tj.text_frame
    tf_tj.word_wrap = True
    add_bullet_item(tf_tj, "T-1 (Membangun Solusi Terpadu):", "Merancang dan membangun aplikasi web admin klinik serta aplikasi mobile pasien yang mencakup 5 modul operasional DSH.", marker="✓ ", pt_size=13.5, marker_color=COLOR_GREEN)
    add_bullet_item(tf_tj, "T-2 (Implementasi Event Asinkron):", "Menerapkan EDA dengan Transactional Outbox Pattern guna menjamin pengiriman event antarmodul secara decoupled dan konsisten.", marker="✓ ", pt_size=13.5, marker_color=COLOR_GREEN)
    add_bullet_item(tf_tj, "T-3 (Implementasi Workflow MHLS):", "Mengimplementasikan Temporal untuk mengotomasi alur program 12 pertemuan, timer reminder mandiri, dan penanganan sinyal laporan pasien.", marker="✓ ", pt_size=13.5, marker_color=COLOR_GREEN)
    add_bullet_item(tf_tj, "T-4 (Evaluasi Fungsional & Failover):", "Mengevaluasi sistem menggunakan Black Box Testing dan membuktikan kemampuan pemulihan (recovery) workflow pasca-gangguan worker.", marker="✓ ", pt_size=13.5, marker_color=COLOR_GREEN)
    add_card_footer_badge(s3, Inches(10.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Sasaran: 4 Tujuan Memenuhi Secara Presisi 4 Rumusan Masalah", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    # =========================================================================
    # SLIDE 4: BATASAN DAN MANFAAT PENELITIAN (2 Columns)
    # =========================================================================
    s4 = new_slides[3]
    c_bat = create_card(s4, Inches(1.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s4, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Ruang Lingkup & Batasan Penelitian", "BATASAN", COLOR_NAVY)
    tb_bat = s4.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf_bat = tb_bat.text_frame
    tf_bat.word_wrap = True
    add_bullet_item(tf_bat, "Objek Penelitian:", "Proses pemantauan klinis perkembangan pasien di Klinik Darus Syifa Holistik (DSH), Serang Banten.", pt_size=13.5)
    add_bullet_item(tf_bat, "Platform yang Dibangun:", "Web Admin untuk operasional tenaga medis klinik & Mobile App bagi pasien program MHLS.", pt_size=13.5)
    add_bullet_item(tf_bat, "Cakupan 5 Modul:", "Data Pasien, Jadwal & Kunjungan, Pemeriksaan Klinis, Monitoring Perkembangan, dan Aktivitas Mandiri.", pt_size=13.5)
    add_bullet_item(tf_bat, "Fokus Arsitektur:", "Penerapan EDA (Transactional Outbox) dan Temporal Workflow Orchestration.", pt_size=13.5)
    add_bullet_item(tf_bat, "Batasan Medis & AI:", "Tidak mencakup diagnosis otomatis, auto-anamnesis, atau rekomendasi resep berbasis AI/ML.", pt_size=13.5)
    add_bullet_item(tf_bat, "Batasan Integrasi Eksternal:", "Tidak mencakup integrasi sistem farmasi obat, kasir/billing, atau bridging BPJS rumah sakit.", pt_size=13.5)
    add_card_footer_badge(s4, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Batas Jelas: Fokus Utama pada Monitoring & Ketahanan Arsitektur", COLOR_CARD_ALT, COLOR_BORDER, COLOR_NAVY)

    c_man = create_card(s4, Inches(10.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s4, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Manfaat Teoritis & Praktis", "MANFAAT", COLOR_TEAL)
    tb_man = s4.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf_man = tb_man.text_frame
    tf_man.word_wrap = True
    add_bullet_item(tf_man, "Manfaat Teoretis:", "Menjadi rujukan ilmiah mengenai penggabungan EDA dan Temporal pada sistem layanan kesehatan berbasis workflow berdurasi panjang.", pt_size=13.5)
    add_bullet_item(tf_man, "Manfaat Klinik DSH:", "Mengeliminasi pencatatan spreadsheet manual, mempercepat input 5 kelompok data klinis, serta menyediakan grafik analitis terpadu.", pt_size=13.5)
    add_bullet_item(tf_man, "Manfaat Pasien MHLS:", "Mempermudah pasien mengajukan jadwal kunjungan dari smartphone, memantau grafik perkembangan, dan disiplin mengisi laporan harian.", pt_size=13.5)
    add_bullet_item(tf_man, "Manfaat Teknis Rekayasa:", "Menghasilkan arsitektur perangkat lunak yang tangguh (fault-tolerant) dan menjamin data consistency tanpa risiko dual-write.", pt_size=13.5)
    add_card_footer_badge(s4, Inches(10.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Dampak Positif: Efisiensi Klinik, Kepatuhan Pasien, & Reliabilitas Sistem", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    # =========================================================================
    # SLIDE 5: STUDI LITERATUR (Clean Modern Table)
    # =========================================================================
    s5 = new_slides[4]
    create_card(s5, Inches(1.2), CONTENT_TOP, Inches(17.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)

    tbl_shape = s5.shapes.add_table(9, 4, Inches(1.3), CONTENT_TOP + Inches(0.15), Inches(17.4), CONTENT_H - Inches(0.3))
    tbl = tbl_shape.table
    tbl.columns[0].width = Inches(3.6)
    tbl.columns[1].width = Inches(4.4)
    tbl.columns[2].width = Inches(4.7)
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
        r.font.size = Pt(12)
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
            cell.fill.fore_color.rgb = COLOR_WHITE if r_idx % 2 == 0 else COLOR_CARD_ALT
            p = cell.text_frame.paragraphs[0]
            p.line_spacing = 1.15
            r = p.add_run()
            r.text = val
            r.font.name = FONT_BODY
            r.font.size = Pt(10.5)
            r.font.bold = (c_idx == 0)
            r.font.color.rgb = COLOR_NAVY if c_idx == 0 else COLOR_TEXT_MAIN

    # =========================================================================
    # SLIDE 6: RESEARCH GAP DAN NOVELTY (2 Cards)
    # =========================================================================
    s6 = new_slides[5]
    c_gap = create_card(s6, Inches(1.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s6, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Research Gap (Kesenjangan Penelitian)", "GAP", COLOR_NAVY)
    tb_gap = s6.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf_gap = tb_gap.text_frame
    tf_gap.word_wrap = True
    add_bullet_item(tf_gap, "Sistem Monitoring Kesehatan Saat Ini:", "Studi terdahulu (Stratil 2026, Nowell 2023) fokus pada formulir pencatatan statis, belum ada mekanisme durable workflow untuk program berdurasi panjang.", pt_size=14)
    add_bullet_item(tf_gap, "Keterbatasan Komunikasi EDA Murni:", "Penelitian EDA (Syarif 2022, Laigner 2026) efektif untuk decoupling pesan instan, namun kesulitan mengelola status alur kerja pasien yang berlangsung selama 3 bulan.", pt_size=14)
    add_bullet_item(tf_gap, "Belum Ada Integrasi EDA + Temporal:", "Berdasarkan telaah pustaka ilmiah terkini, belum ditemukan penelitian yang menggabungkan EDA dan Temporal secara terpadu pada sistem pemantauan klinis.", pt_size=14)
    add_card_footer_badge(s6, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Kesenjangan: Ketiadaan State Persistence pada Alur Klinis Longitudinal", COLOR_SOFT_RED, RGBColor(0xEF, 0x44, 0x44), RGBColor(0x99, 0x1B, 0x1B))

    c_nov = create_card(s6, Inches(10.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s6, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Kebaruan Penelitian (Novelty)", "NOVELTY", COLOR_TEAL)
    tb_nov = s6.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf_nov = tb_nov.text_frame
    tf_nov.word_wrap = True
    add_bullet_item(tf_nov, "Pola Sinergis EDA + Temporal:", "Memadukan EDA (Transactional Outbox) untuk komunikasi antar-modul asinkron dan Temporal untuk orkestrasi workflow aktivitas mandiri berdurasi panjang.", marker="★ ", pt_size=14, marker_color=COLOR_AMBER)
    add_bullet_item(tf_nov, "Durable State Execution:", "Status eksekusi 12 sesi program dijamin persisten; workflow mampu melanjutkan timer dan sinyal secara otomatis meski worker mengalami restart/kegagalan sistem.", marker="★ ", pt_size=14, marker_color=COLOR_AMBER)
    add_bullet_item(tf_nov, "Penerapan Nyata di Klinik Holistik:", "Diterapkan langsung pada protokol klinis Moslem Healthy Lifestyle (DSH) dengan pencatatan terstruktur 5 kelompok data dan 6 analisis grafik perkembangan.", marker="★ ", pt_size=14, marker_color=COLOR_AMBER)
    add_card_footer_badge(s6, Inches(10.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Kontribusi Orisinal: Penjaminan Keberlanjutan Proses Klinis Pasca-Failover", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    # =========================================================================
    # SLIDE 7: TEORI: MONITORING & EDA (2 Cards)
    # =========================================================================
    s7 = new_slides[6]
    c7_1 = create_card(s7, Inches(1.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s7, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Konsep Monitoring Klinis Terstruktur", "MONITORING", COLOR_NAVY)
    tb7_1 = s7.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf7_1 = tb7_1.text_frame
    tf7_1.word_wrap = True
    add_bullet_item(tf7_1, "Definisi Monitoring:", "Pemantauan berkala parameter kesehatan pasien untuk mengevaluasi efektivitas terapi holistik dari waktu ke waktu.", pt_size=14)
    add_bullet_item(tf7_1, "Objektivitas Parameter:", "Menggantikan catatan deskriptif bebas dengan 5 kelompok parameter terukur (Beban Toksin 0-50, Antropometri Tubuh, Tekanan Darah, Lab Metabolik, dan Rasio GKI).", pt_size=14)
    add_bullet_item(tf7_1, "Visualisasi Tren Klinis:", "Penyajian data sesi ke sesi dalam bentuk kurva analitis untuk memudahkan dokter/terapis mengambil keputusan evaluasi.", pt_size=14)
    add_card_footer_badge(s7, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Prinsip: Dari Catatan Bebas Menuju Parameter Kuantitatif Terukur", COLOR_CARD_ALT, COLOR_BORDER, COLOR_NAVY)

    c7_2 = create_card(s7, Inches(10.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s7, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Event-Driven Architecture & Outbox Pattern", "EDA", COLOR_TEAL)
    tb7_2 = s7.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf7_2 = tb7_2.text_frame
    tf7_2.word_wrap = True
    add_bullet_item(tf7_2, "Konsep Domain Event:", "Peristiwa penting yang terjadi dalam domain bisnis (misal: PasienTerdaftar, KunjunganDikonfirmasi, SesiPemeriksaanDicatat).", pt_size=14)
    add_bullet_item(tf7_2, "Komponen EDA:", "Event Producer (backend), Event Broker/Queue, dan Event Consumer/Handler.", pt_size=14)
    add_bullet_item(tf7_2, "Transactional Outbox Pattern:", "Event disimpan ke tabel outbox dalam database transaction atomic yang sama dengan data bisnis, mencegah dual-write failure.", pt_size=14)
    add_bullet_item(tf7_2, "Keuntungan Arsitektural:", "Loose coupling antar-modul, sistem lebih responsif, dan rekam jejak audit (event log) yang lengkap.", pt_size=14)
    add_card_footer_badge(s7, Inches(10.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Keunggulan: Eliminasi Dual-Write Failure Antara DB dan Broker Event", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    # =========================================================================
    # SLIDE 8: TEORI: TEMPORAL & DURABLE WORKFLOW (2 Cards)
    # =========================================================================
    s8 = new_slides[7]
    c8_1 = create_card(s8, Inches(1.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s8, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Prinsip Durable Workflow Orchestration", "WORKFLOW", COLOR_NAVY)
    tb8_1 = s8.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf8_1 = tb8_1.text_frame
    tf8_1.word_wrap = True
    add_bullet_item(tf8_1, "Workflow Jangka Panjang:", "Alur proses bisnis yang berjalan melintasi hari, minggu, hingga berbulan-bulan tanpa memblokir thread sistem.", pt_size=14)
    add_bullet_item(tf8_1, "Durable Execution:", "Status eksekusi (state) alur kerja disimpan secara persisten; proses dapat dihentikan dan dilanjutkan kapan saja tanpa kehilangan konteks.", pt_size=14)
    add_bullet_item(tf8_1, "Event Sourcing History:", "Setiap aksi dicatat sebagai append-only event log; pemulihan proses dilakukan dengan memutar ulang riwayat (replay).", pt_size=14)
    add_card_footer_badge(s8, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Kunci: Eksekusi Tahan Gangguan (Fault-Tolerant Code)", COLOR_CARD_ALT, COLOR_BORDER, COLOR_NAVY)

    c8_2 = create_card(s8, Inches(10.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s8, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Komponen & Kapabilitas Temporal", "TEMPORAL", COLOR_TEAL)
    tb8_2 = s8.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf8_2 = tb8_2.text_frame
    tf8_2.word_wrap = True
    add_bullet_item(tf8_2, "Temporal Server & Cluster:", "Mengelola task queue, history persisten, dan penjadwalan eksekusi alur kerja.", pt_size=14)
    add_bullet_item(tf8_2, "Temporal Worker:", "Layanan background yang mengeksekusi kode alur kerja (workflow) dan pemanggilan fungsi (activity).", pt_size=14)
    add_bullet_item(tf8_2, "Durable Timer:", "Fitur penundaan eksekusi hingga jadwal pengingat tiba tanpa mengonsumsi memori atau thread aktif.", pt_size=14)
    add_bullet_item(tf8_2, "Signal & Query:", "Menerima laporan aktivitas pasien asinkron (Signal) dan memeriksa kepatuhan tanpa mengubah status alur (Query).", pt_size=14)
    add_card_footer_badge(s8, Inches(10.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Kapabilitas: Signal untuk Laporan Pasien & Timer untuk Reminder", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    # =========================================================================
    # SLIDE 9: TEORI: TEKNOLOGI STACK & METODE (2 Cards)
    # =========================================================================
    s9 = new_slides[8]
    c9_1 = create_card(s9, Inches(1.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s9, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Teknologi Sistem yang Digunakan", "TECH STACK", COLOR_NAVY)
    tb9_1 = s9.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf9_1 = tb9_1.text_frame
    tf9_1.word_wrap = True
    add_bullet_item(tf9_1, "Backend Framework (Laravel):", "Penyedia REST API untuk aplikasi mobile dan antarmuka web klinik dengan Eloquent ORM dan Service Layer.", pt_size=14)
    add_bullet_item(tf9_1, "Basis Data Relasional (MySQL 8.0):", "Menyimpan data pasien, riwayat 5 kelompok klinis, jadwal kunjungan, tabel outbox, dan referensi workflow.", pt_size=14)
    add_bullet_item(tf9_1, "Aplikasi Mobile (Flutter):", "Aplikasi lintas platform bagi pasien MHLS untuk mengajukan booking dan mengisi checklist aktivitas mandiri harian.", pt_size=14)
    add_bullet_item(tf9_1, "Kontainerisasi (Docker Compose):", "Mengisolasi seluruh layanan (backend, database, Temporal server, worker, outbox dispatcher) dalam lingkungan konsisten.", pt_size=14)
    add_card_footer_badge(s9, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Infrastruktur: Multi-Container Terisolasi & Konsisten", COLOR_CARD_ALT, COLOR_BORDER, COLOR_NAVY)

    c9_2 = create_card(s9, Inches(10.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s9, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Metode Rekayasa & Evaluasi", "METODE", COLOR_TEAL)
    tb9_2 = s9.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf9_2 = tb9_2.text_frame
    tf9_2.word_wrap = True
    add_bullet_item(tf9_2, "Design Science Research (DSR):", "Pendekatan metodologi ilmiah untuk merancang, mengimplementasikan, dan mengevaluasi artefak teknologi pemecah masalah nyata.", pt_size=14)
    add_bullet_item(tf9_2, "Model Inkremental:", "Pengembangan bertahap berbasis modul layanan (Pendaftaran → Jadwal → Pemeriksaan → Monitoring → Aktivitas Mandiri).", pt_size=14)
    add_bullet_item(tf9_2, "Black Box Testing:", "Pengujian fungsionalitas tombol, validasi formulir, dan integritas proses bisnis klinis.", pt_size=14)
    add_bullet_item(tf9_2, "Uji Ketahanan (Resilience Testing):", "Pengujian failover dengan mematikan container worker secara mendadak saat workflow aktif untuk memverifikasi recovery.", pt_size=14)
    add_card_footer_badge(s9, Inches(10.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Metodologi: DSR Berbasis Masalah Riil dengan Pengujian Ketahanan", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    # =========================================================================
    # SLIDE 10: METODOLOGI PENELITIAN DSR (6 Cards Grid)
    # =========================================================================
    s10 = new_slides[9]
    dsr_stages = [
        ("01", "Identifikasi Masalah", "Monitoring 12 sesi MHLS di Klinik DSH masih manual Excel dan sulit dipantau di luar klinik.", "Artefak: Rumusan Masalah & Kebutuhan DSH", COLOR_NAVY),
        ("02", "Penentuan Solusi", "Menetapkan sasaran sistem web & mobile berbasis EDA dan Temporal untuk otomatisasi pemantauan.", "Artefak: Sasaran Arsitektur & Kriteria Desain", COLOR_TEAL),
        ("03", "Desain & Pengembangan", "Merancang skema database, transactional outbox, dan orkestrasi workflow aktivitas mandiri.", "Artefak: Skema DB, Outbox Code, & Go Worker", COLOR_NAVY),
        ("04", "Demonstrasi Sistem", "Mengoperasikan sistem terintegrasi (web klinik, mobile app, backend API, Temporal, dan MySQL).", "Artefak: Prototipe Web & Mobile Siap Pakai", COLOR_TEAL),
        ("05", "Evaluasi & Pengujian", "Melakukan Black Box Testing fungsionalitas dan uji failover ketahanan worker Temporal saat mati.", "Artefak: Matriks Pengujian & Bukti Recovery", COLOR_NAVY),
        ("06", "Komunikasi Hasil", "Mempresentasikan tugas akhir dan mendokumentasikan kode, arsitektur, serta naskah ilmiah.", "Artefak: Laporan Tugas Akhir & Presentasi", COLOR_TEAL),
    ]

    card_w = Inches(5.6)
    card_h = Inches(3.55)
    for idx, (st_num, st_title, st_desc, st_out, col) in enumerate(dsr_stages):
        r_i = idx // 3
        c_i = idx % 3
        l_pos = Inches(1.2) + c_i * Inches(6.0)
        t_pos = CONTENT_TOP + r_i * Inches(3.8)
        
        cd = create_card(s10, l_pos, t_pos, card_w, card_h, COLOR_WHITE, COLOR_BORDER_TEAL if col == COLOR_TEAL else COLOR_BORDER)
        add_card_header(s10, l_pos + Inches(0.2), t_pos + Inches(0.2), card_w - Inches(0.4), Inches(0.55), st_title, st_num, col)
        
        tb = s10.shapes.add_textbox(l_pos + Inches(0.2), t_pos + Inches(0.85), card_w - Inches(0.4), Inches(1.8))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.line_spacing = 1.2
        r = p.add_run()
        r.text = st_desc
        r.font.name = FONT_BODY
        r.font.size = Pt(13)
        r.font.color.rgb = COLOR_TEXT_MAIN

        out_badge = s10.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, l_pos + Inches(0.2), t_pos + Inches(2.8), card_w - Inches(0.4), Inches(0.55))
        out_badge.fill.solid()
        out_badge.fill.fore_color.rgb = COLOR_SOFT_TEAL if col == COLOR_TEAL else COLOR_CARD_ALT
        out_badge.line.color.rgb = COLOR_BORDER_TEAL if col == COLOR_TEAL else COLOR_BORDER
        out_badge.line.width = Pt(1.0)
        tf_ob = out_badge.text_frame
        tf_ob.word_wrap = True
        p_ob = tf_ob.paragraphs[0]
        p_ob.alignment = PP_ALIGN.CENTER
        r_ob = p_ob.add_run()
        r_ob.text = st_out
        r_ob.font.name = FONT_BODY
        r_ob.font.size = Pt(11)
        r_ob.font.bold = True
        r_ob.font.color.rgb = col

    # =========================================================================
    # SLIDE 11: TAHAPAN PENELITIAN & 5 INCREMENT (5 Horizontal Cards)
    # =========================================================================
    s11 = new_slides[10]
    increments = [
        ("INC-01", "Increment 1: Data Pasien", "Pengelolaan data induk pasien, penerbitan No RM otomatis, serta pemisahan kategori Pasien Umum vs Program MHLS.", "Deliverable: Master Pasien & Token Sanctum", COLOR_NAVY),
        ("INC-02", "Increment 2: Jadwal & Kunjungan", "Sinkronisasi antrean klinik dengan pengajuan booking 12 pertemuan MHLS dari aplikasi mobile pasien.", "Deliverable: Antrean Real-time & Booking App", COLOR_TEAL),
        ("INC-03", "Increment 3: Pemeriksaan Klinis", "Pencatatan 5 kelompok parameter fisik & lab (Toksin 50 keluhan, Tubuh, Vital, Darah, dan Indeks GKI).", "Deliverable: Rekam 5 Kelompok & Auto-GKI", COLOR_NAVY),
        ("INC-04", "Increment 4: Monitoring Perkembangan", "Penyajian 6 kurva grafik analitis untuk mengevaluasi penurunan toksin dan adaptasi ketosis pasien.", "Deliverable: 6 Grafik Analitis Tren Pasien", COLOR_TEAL),
        ("INC-05", "Increment 5: Aktivitas Mandiri MHLS", "Otomatisasi pengingat puasa/gaya hidup via Durable Timer Temporal dan evaluasi kepatuhan harian pasien.", "Deliverable: Durable Timer & Signal Workflow", COLOR_NAVY),
    ]

    for idx, (inc_num, inc_title, inc_desc, inc_out, col) in enumerate(increments):
        t_pos = CONTENT_TOP + idx * Inches(1.48)
        cd = create_card(s11, Inches(1.2), t_pos, Inches(17.6), Inches(1.32), COLOR_WHITE, COLOR_BORDER_TEAL if col == COLOR_TEAL else COLOR_BORDER)
        
        pill = s11.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.4), t_pos + Inches(0.18), Inches(1.6), Inches(0.96))
        pill.fill.solid()
        pill.fill.fore_color.rgb = col
        pill.line.fill.background()
        tf_p = pill.text_frame
        p_p = tf_p.paragraphs[0]
        p_p.alignment = PP_ALIGN.CENTER
        r_p = p_p.add_run()
        r_p.text = inc_num
        r_p.font.name = FONT_HEAD
        r_p.font.size = Pt(13)
        r_p.font.bold = True
        r_p.font.color.rgb = COLOR_WHITE

        tb = s11.shapes.add_textbox(Inches(3.2), t_pos + Inches(0.1), Inches(10.5), Inches(1.1))
        tf = tb.text_frame
        tf.word_wrap = True
        p1 = tf.paragraphs[0]
        p1.line_spacing = 1.15
        r1 = p1.add_run()
        r1.text = inc_title + "\n"
        r1.font.name = FONT_HEAD
        r1.font.size = Pt(15)
        r1.font.bold = True
        r1.font.color.rgb = col

        p2 = tf.add_paragraph()
        p2.line_spacing = 1.15
        r2 = p2.add_run()
        r2.text = inc_desc
        r2.font.name = FONT_BODY
        r2.font.size = Pt(13)
        r2.font.color.rgb = COLOR_TEXT_MAIN

        deliv = s11.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(13.9), t_pos + Inches(0.35), Inches(4.7), Inches(0.6))
        deliv.fill.solid()
        deliv.fill.fore_color.rgb = COLOR_SOFT_TEAL if col == COLOR_TEAL else COLOR_CARD_ALT
        deliv.line.color.rgb = COLOR_BORDER_TEAL if col == COLOR_TEAL else COLOR_BORDER
        deliv.line.width = Pt(1.0)
        tf_d = deliv.text_frame
        tf_d.word_wrap = True
        p_d = tf_d.paragraphs[0]
        p_d.alignment = PP_ALIGN.CENTER
        r_d = p_d.add_run()
        r_d.text = inc_out
        r_d.font.name = FONT_BODY
        r_d.font.size = Pt(11)
        r_d.font.bold = True
        r_d.font.color.rgb = col

    # =========================================================================
    # SLIDE 12: OBJEK PENELITIAN & DATA (2 Cards)
    # =========================================================================
    s12 = new_slides[11]
    c12_1 = create_card(s12, Inches(1.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s12, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Profil Objek Penelitian: Klinik DSH", "OBJEK", COLOR_NAVY)
    tb12_1 = s12.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf12_1 = tb12_1.text_frame
    tf12_1.word_wrap = True
    add_bullet_item(tf12_1, "Nama Objek:", "Klinik Darus Syifa Holistik (DSH).", pt_size=13.5)
    add_bullet_item(tf12_1, "Lokasi Operasional:", "Serang, Provinsi Banten.", pt_size=13.5)
    add_bullet_item(tf12_1, "Pendekatan Pelayanan:", "Pelayanan kesehatan holistik integratif berbasis protokol Moslem Healthy Lifestyle (MHLS).", pt_size=13.5)
    add_bullet_item(tf12_1, "Siklus Terapi:", "12 pertemuan terstruktur selama kurang lebih 3 bulan dengan pemantauan fisik dan mandiri.", pt_size=13.5)
    add_bullet_item(tf12_1, "Urgensi Masalah:", "Pencatatan 12 sesi masih mengandalkan file Excel terpisah tanpa sistem pengingat otomatis di luar klinik.", pt_size=13.5)
    add_card_footer_badge(s12, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Karakteristik: Layanan Holistik Integratif Berbasis Komitmen 3 Bulan", COLOR_CARD_ALT, COLOR_BORDER, COLOR_NAVY)

    c12_2 = create_card(s12, Inches(10.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s12, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Metode Pengumpulan Data", "DATA", COLOR_TEAL)
    tb12_2 = s12.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf12_2 = tb12_2.text_frame
    tf12_2.word_wrap = True
    add_bullet_item(tf12_2, "Observasi Lapangan:", "Mengamati langsung alur pemeriksaan fisik, evaluasi keluhan, dan interaksi pasien dengan terapis.", pt_size=13.5)
    add_bullet_item(tf12_2, "Wawancara Semi-Terstruktur:", "Wawancara dengan pimpinan klinik (Ust. Inayatullah, M.Pd) mengenai aturan 12 pertemuan MHLS.", pt_size=13.5)
    add_bullet_item(tf12_2, "Studi Dokumen & SOP Klinik:", "Menganalisis kuesioner 50 indikator toksin, rentang target IMT, serta formula resmi perhitungan GKI.", pt_size=13.5)
    add_bullet_item(tf12_2, "Studi Kepustakaan Ilmiah:", "Menelaah literatur jurnal internasional terkait Event-Driven Architecture, Outbox Pattern, dan Temporal.", pt_size=13.5)
    add_card_footer_badge(s12, Inches(10.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Validitas Data: Triangulasi Sumber Data Primer & Standar SOP Resmi Klinik", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    # =========================================================================
    # SLIDE 13: PROSES BISNIS BERJALAN (Swimlane 2 Aktor: Pasien vs Klinik)
    # =========================================================================
    s13 = new_slides[12]
    c13_p = create_card(s13, Inches(1.2), CONTENT_TOP, Inches(8.6), Inches(5.85), COLOR_WHITE, COLOR_BORDER)
    add_card_header(s13, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Aktivitas Pasien (Tatap Muka & Mandiri)", "AKTOR 1: PASIEN", COLOR_NAVY)
    tb13_p = s13.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.85), Inches(8.2), Inches(4.8))
    tf13_p = tb13_p.text_frame
    tf13_p.word_wrap = True
    add_bullet_item(tf13_p, "1. Datang Mendaftar:", "Pasien datang langsung ke klinik dan mengisi buku tamu pendaftaran fisik.", pt_size=13)
    add_bullet_item(tf13_p, "2. Menjalani Pemeriksaan:", "Pasien diperiksa tanda vital, keluhan toksin, dan diterapi oleh terapis.", pt_size=13)
    add_bullet_item(tf13_p, "3. Terima Instruksi Cetak:", "Menerima lembaran kertas panduan puasa/aktivitas mandiri untuk dibawa pulang.", pt_size=13)
    add_bullet_item(tf13_p, "4. Menjalankan di Rumah:", "Menjalani instruksi tanpa adanya media pelaporan atau pengingat otomatis berkala.", pt_size=13)
    add_bullet_item(tf13_p, "5. Datang Pertemuan Berikutnya:", "Menceritakan kembali kepatuhan aktivitas saat sesi konsultasi berikutnya tiba.", pt_size=13.5)

    c13_k = create_card(s13, Inches(10.2), CONTENT_TOP, Inches(8.6), Inches(5.85), COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s13, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Aktivitas Pihak Klinik (Petugas & Terapis)", "AKTOR 2: KLINIK DSH", COLOR_TEAL)
    tb13_k = s13.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.85), Inches(8.2), Inches(4.8))
    tf13_k = tb13_k.text_frame
    tf13_k.word_wrap = True
    add_bullet_item(tf13_k, "1. Registrasi & Klasifikasi:", "Mencatat biodata pasien ke buku tamu dan mengklasifikasikan Pasien Umum vs MHLS.", pt_size=13)
    add_bullet_item(tf13_k, "2. Pemeriksaan & Input Excel:", "Mencatat hasil tensi, antropometri, toksin, dan lab ke spreadsheet Excel manual.", pt_size=13)
    add_bullet_item(tf13_k, "3. Edukasi Aktivitas Mandiri:", "Menjelaskan pola real food, jam puasa, dan memberikan lembaran instruksi cetak.", pt_size=13)
    add_bullet_item(tf13_k, "4. Ketiadaan Pemantauan Rumah:", "Klinik tidak memiliki sarana memantau apakah pasien disiplin puasa di rumah.", pt_size=13)
    add_bullet_item(tf13_k, "5. Evaluasi Kuis Tertunda:", "Kepatuhan pasien baru dinilai secara tanya-jawab lisan saat kunjungan ulang.", pt_size=13)

    bot_card = s13.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.2), CONTENT_TOP + Inches(6.05), Inches(17.6), Inches(1.25))
    bot_card.fill.solid()
    bot_card.fill.fore_color.rgb = COLOR_SOFT_RED
    bot_card.line.color.rgb = RGBColor(0xEF, 0x44, 0x44)
    bot_card.line.width = Pt(1.5)
    tf_b = bot_card.text_frame
    tf_b.word_wrap = True
    p_b = tf_b.paragraphs[0]
    p_b.line_spacing = 1.15
    r_bh = p_b.add_run()
    r_bh.text = "Kelemahan & Titik Kritis Proses Bisnis Berjalan:\n"
    r_bh.font.name = FONT_HEAD
    r_bh.font.size = Pt(12.5)
    r_bh.font.bold = True
    r_bh.font.color.rgb = RGBColor(0x99, 0x1B, 0x1B)

    r_bd = p_b.add_run()
    r_bd.text = "1) Riwayat klinis tercecer di multi-file Excel terpisah.  2) Ketiadaan media komunikasi dan pengingat harian selama di rumah.  3) Evaluasi kepatuhan bersifat retrospektif dan sering terlambat diintervensi oleh terapis."
    r_bd.font.name = FONT_BODY
    r_bd.font.size = Pt(12)
    r_bd.font.color.rgb = RGBColor(0x7F, 0x1D, 0x1D)

    # =========================================================================
    # SLIDE 14: PROSES BISNIS DIUSULKAN (Swimlane 3 Aktor: Pasien, Klinik, Sistem)
    # =========================================================================
    s14 = new_slides[13]
    col_w = Inches(5.6)
    
    c14_1 = create_card(s14, Inches(1.2), CONTENT_TOP, col_w, Inches(5.85), COLOR_WHITE, COLOR_BORDER)
    add_card_header(s14, Inches(1.4), CONTENT_TOP + Inches(0.2), col_w - Inches(0.4), Inches(0.55), "Aplikasi Mobile Pasien", "PASIEN", COLOR_NAVY)
    tb14_1 = s14.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.85), col_w - Inches(0.4), Inches(4.8))
    tf14_1 = tb14_1.text_frame
    tf14_1.word_wrap = True
    add_bullet_item(tf14_1, "1. Login Akun Mobile:", "Masuk akun yang diterbitkan klinik setelah terdaftar program MHLS.", pt_size=12.5)
    add_bullet_item(tf14_1, "2. Booking Pertemuan:", "Mengajukan slot kunjungan (1-12) langsung via smartphone.", pt_size=12.5)
    add_bullet_item(tf14_1, "3. Notifikasi Pengingat:", "Menerima alert jam puasa dan checklist harian dari sistem.", pt_size=12.5)
    add_bullet_item(tf14_1, "4. Kirim Laporan Sinyal:", "Melaporkan kepatuhan aktivitas mandiri real-time ke sistem.", pt_size=12.5)
    add_bullet_item(tf14_1, "5. Pantau Grafik Progres:", "Melihat grafik penurunan toksin & target GKI secara berkala.", pt_size=12.5)

    c14_2 = create_card(s14, Inches(7.2), CONTENT_TOP, col_w, Inches(5.85), COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s14, Inches(7.4), CONTENT_TOP + Inches(0.2), col_w - Inches(0.4), Inches(0.55), "Web Admin Klinik", "KLINIK DSH", COLOR_TEAL)
    tb14_2 = s14.shapes.add_textbox(Inches(7.4), CONTENT_TOP + Inches(0.85), col_w - Inches(0.4), Inches(4.8))
    tf14_2 = tb14_2.text_frame
    tf14_2.word_wrap = True
    add_bullet_item(tf14_2, "1. Pendaftaran Digital:", "Registrasi data induk pasien dan penetapan program MHLS.", pt_size=12.5)
    add_bullet_item(tf14_2, "2. Konfirmasi Antrean:", "Menyetujui jadwal booking; otomatis muncul di antrean periksa.", pt_size=12.5)
    add_bullet_item(tf14_2, "3. Input 5 Kelompok Klinis:", "Pencatatan terstruktur: Toksin 50, Tubuh, Vital, Darah, GKI.", pt_size=12.5)
    add_bullet_item(tf14_2, "4. Susun Instruksi Mandiri:", "Menetapkan rencana gaya hidup yang disinkronkan ke Temporal.", pt_size=12.5)
    add_bullet_item(tf14_2, "5. Monitoring Real-Time:", "Memantau kurva analitis perkembangan pasien sesi ke sesi.", pt_size=12.5)

    c14_3 = create_card(s14, Inches(13.2), CONTENT_TOP, col_w, Inches(5.85), COLOR_WHITE, COLOR_BORDER)
    add_card_header(s14, Inches(13.4), CONTENT_TOP + Inches(0.2), col_w - Inches(0.4), Inches(0.55), "EDA & Temporal Engine", "SISTEM", COLOR_NAVY)
    tb14_3 = s14.shapes.add_textbox(Inches(13.4), CONTENT_TOP + Inches(0.85), col_w - Inches(0.4), Inches(4.8))
    tf14_3 = tb14_3.text_frame
    tf14_3.word_wrap = True
    add_bullet_item(tf14_3, "1. Domain Event Publishing:", "Menerbitkan event PasienTerdaftar & KunjunganDikonfirmasi.", pt_size=12.5)
    add_bullet_item(tf14_3, "2. Transactional Outbox:", "Menyimpan event dalam database transaction mencegah dual-write.", pt_size=12.5)
    add_bullet_item(tf14_3, "3. Durable Workflow Execution:", "Temporal mengelola timer pengingat aktivitas jangka panjang.", pt_size=12.5)
    add_bullet_item(tf14_3, "4. Signal Handler:", "Menerima laporan pasien dan memperbarui state tanpa blocking.", pt_size=12.5)
    add_bullet_item(tf14_3, "5. Replay Pasca-Failover:", "Workflow otomatis pulih jika server/worker mengalami kegagalan.", pt_size=12.5)

    val_card = s14.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.2), CONTENT_TOP + Inches(6.05), Inches(17.6), Inches(1.25))
    val_card.fill.solid()
    val_card.fill.fore_color.rgb = COLOR_SOFT_TEAL
    val_card.line.color.rgb = RGBColor(0x10, 0xB9, 0x81)
    val_card.line.width = Pt(1.5)
    tf_v = val_card.text_frame
    tf_v.word_wrap = True
    p_v = tf_v.paragraphs[0]
    p_v.line_spacing = 1.15
    r_vh = p_v.add_run()
    r_vh.text = "Keunggulan Proses Bisnis yang Diusulkan:\n"
    r_vh.font.name = FONT_HEAD
    r_vh.font.size = Pt(12.5)
    r_vh.font.bold = True
    r_vh.font.color.rgb = RGBColor(0x06, 0x5F, 0x46)

    r_vd = p_v.add_run()
    r_vd.text = "1) Penautan Otomatis: Jadwal booking pasien langsung berstatus 'Belum Diisi' di antrean pemeriksaan.  2) Pemantauan Kontinu: Kepatuhan di rumah terukur real-time via Durable Timer.  3) Keandalan Tinggi: Konsistensi data transaksi terjaga tanpa risiko data hilang."
    r_vd.font.name = FONT_BODY
    r_vd.font.size = Pt(12)
    r_vd.font.color.rgb = RGBColor(0x04, 0x4E, 0x39)

    # =========================================================================
    # SLIDE 15: ARSITEKTUR SISTEM (4 Layer Cards)
    # =========================================================================
    s15 = new_slides[14]
    layers = [
        ("LAYER 1", "Presentation & Client Layer", "Web Admin Klinik (Blade/Tailwind/Chart.js) untuk tata kelola medis & Mobile App Pasien (Flutter 3.44) untuk pelaporan mandiri.", "Komponen: Web Admin, Mobile Flutter, Sanctum Auth Token", COLOR_NAVY),
        ("LAYER 2", "Backend & API Service Layer", "Laravel RESTful API melayani logika bisnis pendaftaran, booking, input 5 kelompok klinis, dan transaksi basis data.", "Komponen: Laravel 11, Service Layer, Eloquent ORM, MySQL 8.0", COLOR_TEAL),
        ("LAYER 3", "Asynchronous Event Communication Layer", "Pola Transactional Outbox menjamin domain event tersimpan atomic bersama transaksi bisnis dan disalurkan secara decoupled.", "Komponen: MySQL outbox_events, Go Outbox Dispatcher, Event Bus", COLOR_NAVY),
        ("LAYER 4", "Durable Workflow Orchestration Layer", "Temporal Server & Worker mengelola alur kerja jangka panjang 12 pertemuan, timer pengingat otomatis, dan replay event pasca-failover.", "Komponen: Temporal Server v1.24, Go Worker, Durable Timer, Replay History", COLOR_TEAL),
    ]

    for idx, (lay_num, lay_t, lay_d, lay_c, col) in enumerate(layers):
        t_pos = CONTENT_TOP + idx * Inches(1.85)
        cd = create_card(s15, Inches(1.2), t_pos, Inches(17.6), Inches(1.65), COLOR_WHITE, COLOR_BORDER_TEAL if col == COLOR_TEAL else COLOR_BORDER)
        
        pill = s15.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.4), t_pos + Inches(0.25), Inches(1.8), Inches(1.15))
        pill.fill.solid()
        pill.fill.fore_color.rgb = col
        pill.line.fill.background()
        tf_p = pill.text_frame
        p_p = tf_p.paragraphs[0]
        p_p.alignment = PP_ALIGN.CENTER
        r_p = p_p.add_run()
        r_p.text = lay_num
        r_p.font.name = FONT_HEAD
        r_p.font.size = Pt(13)
        r_p.font.bold = True
        r_p.font.color.rgb = COLOR_WHITE

        tb = s15.shapes.add_textbox(Inches(3.4), t_pos + Inches(0.12), Inches(9.8), Inches(1.4))
        tf = tb.text_frame
        tf.word_wrap = True
        p1 = tf.paragraphs[0]
        p1.line_spacing = 1.15
        r1 = p1.add_run()
        r1.text = lay_t + "\n"
        r1.font.name = FONT_HEAD
        r1.font.size = Pt(15.5)
        r1.font.bold = True
        r1.font.color.rgb = col

        p2 = tf.add_paragraph()
        p2.line_spacing = 1.15
        r2 = p2.add_run()
        r2.text = lay_d
        r2.font.name = FONT_BODY
        r2.font.size = Pt(13)
        r2.font.color.rgb = COLOR_TEXT_MAIN

        c_badge = s15.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(13.5), t_pos + Inches(0.35), Inches(5.1), Inches(0.95))
        c_badge.fill.solid()
        c_badge.fill.fore_color.rgb = COLOR_SOFT_TEAL if col == COLOR_TEAL else COLOR_CARD_ALT
        c_badge.line.color.rgb = COLOR_BORDER_TEAL if col == COLOR_TEAL else COLOR_BORDER
        c_badge.line.width = Pt(1.0)
        tf_cb = c_badge.text_frame
        tf_cb.word_wrap = True
        p_cb = tf_cb.paragraphs[0]
        p_cb.alignment = PP_ALIGN.CENTER
        r_cb = p_cb.add_run()
        r_cb.text = lay_c
        r_cb.font.name = FONT_BODY
        r_cb.font.size = Pt(11)
        r_cb.font.bold = True
        r_cb.font.color.rgb = col

    # =========================================================================
    # SLIDE 16: MODUL & BASIS DATA (2 Cards)
    # =========================================================================
    s16 = new_slides[15]
    c16_1 = create_card(s16, Inches(1.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s16, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "5 Modul Fungsional Layanan Klinik", "MODUL", COLOR_NAVY)
    tb16_1 = s16.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf16_1 = tb16_1.text_frame
    tf16_1.word_wrap = True
    add_bullet_item(tf16_1, "1. Modul Data Pasien:", "Pengelolaan profil, No RM otomatis, & klasifikasi pasien Umum vs Program MHLS.", pt_size=13.5)
    add_bullet_item(tf16_1, "2. Modul Jadwal & Kunjungan:", "Sinkronisasi antrean klinik dengan pengajuan booking 12 pertemuan dari aplikasi mobile.", pt_size=13.5)
    add_bullet_item(tf16_1, "3. Modul Pemeriksaan Klinis:", "Pencatatan 5 kelompok parameter fisik & lab (Toksin 50 indikator, Tubuh, Vital, Darah, & GKI).", pt_size=13.5)
    add_bullet_item(tf16_1, "4. Modul Monitoring Perkembangan:", "Visualisasi 6 grafik analitis untuk mengevaluasi progres penurunan toksin & ketosis.", pt_size=13.5)
    add_bullet_item(tf16_1, "5. Modul Aktivitas Mandiri MHLS:", "Pengelolaan instruksi gaya hidup, jadwal pengingat, dan orkestrasi workflow Temporal.", pt_size=13.5)
    add_card_footer_badge(s16, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Navigasi: 5 Modul Klinis Terstandarisasi Filament-Style", COLOR_CARD_ALT, COLOR_BORDER, COLOR_NAVY)

    c16_2 = create_card(s16, Inches(10.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s16, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Entitas Basis Data Utama (MySQL 8.0)", "DATABASE", COLOR_TEAL)
    tb16_2 = s16.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf16_2 = tb16_2.text_frame
    tf16_2.word_wrap = True
    add_bullet_item(tf16_2, "users & patients:", "Kredensial autentikasi Sanctum dan data demografi pasien.", pt_size=13.5)
    add_bullet_item(tf16_2, "visits:", "Tanggal, slot waktu, nomor pertemuan (1-12), dan status booking kunjungan.", pt_size=13.5)
    add_bullet_item(tf16_2, "patient_progress_sessions:", "Menghubungkan kunjungan dengan rekam evaluasi klinis dan catatan staff.", pt_size=13.5)
    add_bullet_item(tf16_2, "toxin, body, & blood_measurements:", "Detail 50 keluhan toksin, komposisi berat/IMT, tekanan darah, glukosa, keton, & GKI.", pt_size=13.5)
    add_bullet_item(tf16_2, "activity_plans & schedules:", "Instruksi hidup sehat, periode waktu, frekuensi, dan tracking laporan pasien.", pt_size=13.5)
    add_bullet_item(tf16_2, "outbox_events & workflow_references:", "Pencatatan transactional outbox event dan mapping ID workflow Temporal.", pt_size=13.5)
    add_card_footer_badge(s16, Inches(10.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Data Klinis: 5 Kelompok Pengukuran Fisik + 1 Aktivitas Mandiri", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    # =========================================================================
    # SLIDE 17: IMPLEMENTASI & PENGUJIAN (2 Cards)
    # =========================================================================
    s17 = new_slides[16]
    c17_1 = create_card(s17, Inches(1.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER)
    add_card_header(s17, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Lingkungan Implementasi Sistem", "LINGKUNGAN", COLOR_NAVY)
    tb17_1 = s17.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf17_1 = tb17_1.text_frame
    tf17_1.word_wrap = True
    add_bullet_item(tf17_1, "Kontainerisasi Docker Compose:", "Layanan backend API, MySQL 8.0, Temporal Server, Temporal UI, Worker Go, dan Outbox Dispatcher berjalan terisolasi.", pt_size=14)
    add_bullet_item(tf17_1, "Backend REST API (Laravel):", "Melayani autentikasi token Sanctum, validasi transaksi, dan integrasi antar-modul klinik.", pt_size=14)
    add_bullet_item(tf17_1, "Antarmuka Modern Medis:", "Mengusung palet warna resmi Klinik DSH (#20596c), layout Filament yang bersih, dan visualisasi interaktif Chart.js.", pt_size=14)
    add_bullet_item(tf17_1, "Aplikasi Mobile Flutter:", "Aplikasi mobile responsif terhubung ke REST API dengan state management terstruktur.", pt_size=14)
    add_card_footer_badge(s17, Inches(1.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Infrastruktur: 6 Service Terkontainerisasi Siap Produksi", COLOR_CARD_ALT, COLOR_BORDER, COLOR_NAVY)

    c17_2 = create_card(s17, Inches(10.2), CONTENT_TOP, Inches(8.6), CONTENT_H, COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s17, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Skenario Pengujian Sistem", "PENGUJIAN", COLOR_TEAL)
    tb17_2 = s17.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.9), Inches(8.2), Inches(5.4))
    tf17_2 = tb17_2.text_frame
    tf17_2.word_wrap = True
    add_bullet_item(tf17_2, "Black Box Testing (Fungsional):", "Memverifikasi seluruh fungsionalitas registrasi pasien, booking jadwal, input 5 kelompok klinis, dan pelaporan mobile.", pt_size=14)
    add_bullet_item(tf17_2, "Pengujian Alur Event EDA:", "Memastikan domain event tersimpan dalam database transaction yang sama dan terkirim tanpa dual-write failure.", pt_size=14)
    add_bullet_item(tf17_2, "Pengujian Ketahanan Worker Temporal:", "Menguji penghentian paksa worker saat workflow aktif; membuktikan pemulihan timer dan kelanjutan proses otomatis.", pt_size=14)
    add_bullet_item(tf17_2, "Evaluasi Usability (SUS):", "Rencana pengukuran tingkat kegunaan antarmuka web dan mobile kepada petugas klinik dan pasien.", pt_size=14)
    add_card_footer_badge(s17, Inches(10.4), CONTENT_TOP + Inches(6.55), Inches(8.2), Inches(0.55), "Metode Uji: Verifikasi Fungsional, Event Integrity, & Worker Failover", COLOR_SOFT_TEAL, COLOR_BORDER_TEAL, COLOR_TEAL)

    # =========================================================================
    # SLIDE 18: HASIL IMPLEMENTASI & PEMBAHASAN (Stat KPIs + 2 Cards)
    # =========================================================================
    s18 = new_slides[17]
    add_kpi_box(s18, Inches(1.2), CONTENT_TOP, Inches(4.1), Inches(1.3), "12 Pertemuan", "Siklus Terstruktur MHLS", COLOR_NAVY)
    add_kpi_box(s18, Inches(5.7), CONTENT_TOP, Inches(4.1), Inches(1.3), "5 Kelompok Data", "Parameter Fisik & Klinis", COLOR_TEAL)
    add_kpi_box(s18, Inches(10.2), CONTENT_TOP, Inches(4.1), Inches(1.3), "6 Kurva Analitis", "Monitoring Progres Pasien", COLOR_NAVY)
    add_kpi_box(s18, Inches(14.7), CONTENT_TOP, Inches(4.1), Inches(1.3), "100% Recovery", "Ketahanan Failover Worker", COLOR_TEAL)

    c18_1 = create_card(s18, Inches(1.2), CONTENT_TOP + Inches(1.5), Inches(8.6), CONTENT_H - Inches(1.5), COLOR_WHITE, COLOR_BORDER)
    add_card_header(s18, Inches(1.4), CONTENT_TOP + Inches(1.7), Inches(8.2), Inches(0.55), "Hasil Fungsionalitas Aplikasi Web & Mobile", "HASIL", COLOR_NAVY)
    tb18_1 = s18.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(2.4), Inches(8.2), Inches(4.8))
    tf18_1 = tb18_1.text_frame
    tf18_1.word_wrap = True
    add_bullet_item(tf18_1, "Dashboard Bersih & Klinis:", "Menyajikan ringkasan operasional pasien, grafik tren harian, antrean periksa, dan riwayat klinis tanpa polusi data teknis.", pt_size=13.5)
    add_bullet_item(tf18_1, "Pemeriksaan 5 Kelompok:", "Pencatatan 50 keluhan toksin, tubuh, tekanan darah, metabolik, dan kalkulasi otomatis indeks GKI berfungsi presisi.", pt_size=13.5)
    add_bullet_item(tf18_1, "6 Visualisasi Grafik Analitis:", "Kurva analitis berhasil memetakan penurunan toksin (18 ke 3) dan pencapaian zona ketosis terapeutik pasien MHLS.", pt_size=13.5)
    add_bullet_item(tf18_1, "Integrasi Mobile App Pasien:", "Pasien dapat melihat jadwal terkonfirmasi dan melaporkan checklist aktivitas mandiri harian secara real-time.", pt_size=13.5)

    c18_2 = create_card(s18, Inches(10.2), CONTENT_TOP + Inches(1.5), Inches(8.6), CONTENT_H - Inches(1.5), COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s18, Inches(10.4), CONTENT_TOP + Inches(1.7), Inches(8.2), Inches(0.55), "Pembahasan Keandalan EDA & Temporal", "PEMBAHASAN", COLOR_TEAL)
    tb18_2 = s18.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(2.4), Inches(8.2), Inches(4.8))
    tf18_2 = tb18_2.text_frame
    tf18_2.word_wrap = True
    add_bullet_item(tf18_2, "Integritas Event EDA:", "Pola Transactional Outbox terbukti mengeliminasi risiko dual-write failure antara database MySQL dan broker event.", pt_size=13.5)
    add_bullet_item(tf18_2, "Orkestrasi Jangka Panjang:", "Temporal berhasil mengelola durasi tunggu program 12 sesi menggunakan Durable Timer yang efisien sumber daya.", pt_size=13.5)
    add_bullet_item(tf18_2, "Ketahanan Failover (Resilience):", "Uji terminasi worker membuktikan workflow tetap persisten di server dan langsung dilanjutkan saat worker hidup kembali.", pt_size=13.5)
    add_bullet_item(tf18_2, "Otomasi Penautan Jadwal:", "Jadwal booking kunjungan dari pasien langsung tertaut otomatis sebagai antrean 'Belum Diisi' pada modul pemeriksaan klinik.", pt_size=13.5)

    # =========================================================================
    # SLIDE 19: KESIMPULAN, SARAN & DAFTAR PUSTAKA (3 Structured Cards)
    # =========================================================================
    s19 = new_slides[18]
    c19_k = create_card(s19, Inches(1.2), CONTENT_TOP, Inches(8.6), Inches(4.8), COLOR_WHITE, COLOR_BORDER)
    add_card_header(s19, Inches(1.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Kesimpulan Penelitian", "KESIMPULAN", COLOR_NAVY)
    tb19_k = s19.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(0.85), Inches(8.2), Inches(3.8))
    tf19_k = tb19_k.text_frame
    tf19_k.word_wrap = True
    add_bullet_item(tf19_k, "K-1 (Penyelesaian Masalah):", "Sistem berhasil menggantikan pencatatan spreadsheet manual menjadi platform digital terpadu dengan 5 modul klinis.", pt_size=13)
    add_bullet_item(tf19_k, "K-2 (Efektivitas EDA):", "Penerapan EDA via Transactional Outbox menjamin konsistensi data transaksi dan mempermudah integrasi asinkron antarbagian.", pt_size=13)
    add_bullet_item(tf19_k, "K-3 (Keandalan Temporal):", "Temporal terbukti andal mengelola alur program MHLS jangka panjang dan tahan terhadap gangguan worker tanpa kehilangan state.", pt_size=13)

    c19_s = create_card(s19, Inches(10.2), CONTENT_TOP, Inches(8.6), Inches(4.8), COLOR_WHITE, COLOR_BORDER_TEAL)
    add_card_header(s19, Inches(10.4), CONTENT_TOP + Inches(0.2), Inches(8.2), Inches(0.55), "Saran Pengembangan Lanjutan", "SARAN", COLOR_TEAL)
    tb19_s = s19.shapes.add_textbox(Inches(10.4), CONTENT_TOP + Inches(0.85), Inches(8.2), Inches(3.8))
    tf19_s = tb19_s.text_frame
    tf19_s.word_wrap = True
    add_bullet_item(tf19_s, "S-1 (Integrasi Perangkat IoT):", "Integrasi langsung dengan alat timbang bioimpedansi dan glucometer Bluetooth untuk input otomatis tanpa ketik manual.", pt_size=13)
    add_bullet_item(tf19_s, "S-2 (Notifikasi Multikanal):", "Penambahan channel notifikasi pengingat via WhatsApp Gateway selain push notification aplikasi mobile.", pt_size=13)
    add_bullet_item(tf19_s, "S-3 (Skalabilitas Multi-Klinik):", "Pengembangan arsitektur multi-tenant untuk mendukung operasional cabang klinik holistik di berbagai kota.", pt_size=13)

    c19_p = create_card(s19, Inches(1.2), CONTENT_TOP + Inches(5.0), Inches(17.6), Inches(2.35), COLOR_WHITE, COLOR_BORDER)
    add_card_header(s19, Inches(1.4), CONTENT_TOP + Inches(5.15), Inches(17.2), Inches(0.45), "Daftar Pustaka Utama (Format APA 7)", "PUSTAKA", COLOR_NAVY)
    tb19_p = s19.shapes.add_textbox(Inches(1.4), CONTENT_TOP + Inches(5.65), Inches(17.2), Inches(1.6))
    tf19_p = tb19_p.text_frame
    tf19_p.word_wrap = True

    pustaka = [
        "• Nagpal, V. (2026). Comparative Analysis of Workflow Orchestration Frameworks for Resilient Distributed Systems. Journal of Software Engineering, 18(2), 112-125.",
        "• Nowell, W. B., et al. (2023). Patient Adherence and Longitudinal Monitoring in Digital Health Applications. BMC Medical Informatics, 23(1), 45-58.",
        "• Syarif, M., & Pizaini. (2022). Penerapan Event-Driven Architecture pada Sistem Informasi Berbasis Microservices. Jurnal Rekayasa Sistem, 8(3), 201-210.",
        "• Yasini, M., et al. (2025). Identification and Categorization of Clinical Events in Healthcare Digitalization. IEEE Access, 13, 34210-34224."
    ]
    for pus in pustaka:
        p = tf19_p.add_paragraph() if tf19_p.paragraphs[0].text else tf19_p.paragraphs[0]
        p.space_after = Pt(2)
        r = p.add_run()
        r.text = pus
        r.font.name = FONT_BODY
        r.font.size = Pt(11)
        r.font.color.rgb = COLOR_TEXT_MUTED

    # =========================================================================
    # SLIDE 20: TERIMA KASIH (Pertahankan Asli Template Slide 12)
    # =========================================================================
    s20 = new_slides[19]

    prs.save(output_path)
    print(f"Presentation successfully enhanced & saved to {output_path} with {len(prs.slides)} slides!")

if __name__ == '__main__':
    build_presentation()
