"""
Interactive Web Application for ML Reliability Stress Testing Framework (Version 1.1.0).
Complies with Section 16 & 23 of Master Prompt and Addresses All Critiques in perbaikan.md:
  1. Dataset Management (Credit Default, Nutrition 'nilai-gizi', Custom CSV)
  2. Experiment Configuration (Internal Baseline Training or External .pkl Model Upload)
  3. Shift Configuration (Scenarios A, B, C, D Categorical, E Pure Subsampling + Support Overlap Check)
  4. Distribution Distance (MMD RBF + Sliced Wasserstein)
  5. Model Evaluation (5-Fold CV, Degradation Assessment)
  6. Reliability Analysis (Dynamic Threshold, Per-Scenario & Overall Spearman Correlation with p-values)
  7. History & Export (Schema 1.1.0 Provenance, CSV, JSON, Markdown)
"""

import os
import json
import tempfile
import numpy as np
import pandas as pd
import streamlit as st
import matplotlib.pyplot as plt
import seaborn as sns

from framework.config import (
    ExperimentConfig,
    ShiftScenario,
    ShiftSeverity,
    ModelAlgorithm,
    ShiftType,
    ThresholdStatus,
    SCHEMA_VERSION,
    FRAMEWORK_VERSION
)
from framework.dataset.validator import DatasetValidator
from framework.dataset.loader import (
    split_train_test,
    generate_credit_default_dataset,
    generate_nutrition_dataset
)
from framework.dataset.preprocessor import TabularPreprocessor
from framework.shift.generator import ShiftGenerator
from framework.distance.mmd import calculate_mmd
from framework.distance.wasserstein import calculate_wasserstein
from framework.models.baseline import BaselineTrainer
from framework.models.loader import ExternalModelLoader
from framework.models.evaluator import ModelEvaluator
from framework.reliability.degradation import DegradationCalculator
from framework.reliability.threshold import ReliabilityThresholdChecker
from framework.reliability.profile import ReliabilityProfile
from framework.pipeline import StressTestingPipeline

# Page setup
st.set_page_config(
    page_title="ML Reliability Stress Testing Framework v1.1.0",
    page_icon="🛡️",
    layout="wide",
    initial_sidebar_state="expanded"
)

# Custom CSS for polished research aesthetic
st.markdown("""
<style>
    .main-header {
        font-size: 26px;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 4px;
    }
    .sub-header {
        font-size: 14px;
        color: #64748B;
        margin-bottom: 20px;
    }
    .badge-active {
        background-color: #DCFCE7;
        color: #166534;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-disabled {
        background-color: #F1F5F9;
        color: #94A3B8;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
</style>
""", unsafe_allow_html=True)


# Initialize Session State
if "dataset" not in st.session_state:
    st.session_state.dataset = generate_credit_default_dataset(n_samples=2500, random_seed=42)
    st.session_state.dataset_name = "Credit Card Default Benchmark"
    st.session_state.target_col = "default"

if "pipeline_run" not in st.session_state:
    st.session_state.pipeline_run = False
    st.session_state.profile = None
    st.session_state.pipeline = None
    st.session_state.external_model_path = None

# Sidebar Navigation
st.sidebar.title("🛡️ ML Stress Testing")
st.sidebar.markdown(f"**Framework Version `{FRAMEWORK_VERSION}` (Schema `{SCHEMA_VERSION}`)**")
page = st.sidebar.radio(
    "Tahapan Sistem:",
    [
        "1. Dataset Management",
        "2. Experiment Configuration",
        "3. Shift Configuration",
        "4. Distribution Distance",
        "5. Model Evaluation",
        "6. Reliability Analysis",
        "7. History & Export"
    ]
)

st.sidebar.markdown("---")
st.sidebar.info(
    "**Spesifikasi Metodologis (v1.1.0):**\n"
    "- Shift Type: Covariate Shift\n"
    "- Skenario: A (Mean), B (Var), C (Outlier), D (Cat), E (Subsample)\n"
    "- Validasi: Zero Leakage & Support Overlap\n"
    "- Model: LogReg, XGBoost, atau Pretrained .pkl\n"
    "- Distance: MMD (RBF) & Sliced Wasserstein\n"
    "- Threshold: 1.5x Std Dev CV"
)


# ==========================================
# PAGE 1: DATASET MANAGEMENT
# ==========================================
if page == "1. Dataset Management":
    st.markdown("<div class='main-header'>Halaman 1 — Dataset Management & Validasi Tabular</div>", unsafe_allow_html=True)
    st.markdown("<div class='sub-header'>Unggah dataset tabular atau gunakan benchmark terstandarisasi untuk evaluasi ketahanan.</div>", unsafe_allow_html=True)

    col1, col2 = st.columns([1, 2])
    with col1:
        st.subheader("Pilih Sumber Data")
        data_source = st.radio(
            "Sumber Dataset:",
            [
                "Benchmark 1: Credit Card Default",
                "Benchmark 2: Klasifikasi Gizi ('nilai-gizi')",
                "Upload CSV Kustom"
            ]
        )

        if data_source == "Upload CSV Kustom":
            uploaded_file = st.file_uploader("Upload CSV Tabular", type=["csv"])
            if uploaded_file is not None:
                try:
                    df_uploaded = pd.read_csv(uploaded_file)
                    st.session_state.dataset = df_uploaded
                    st.session_state.dataset_name = uploaded_file.name
                    st.session_state.pipeline_run = False
                    st.success(f"Berhasil memuat dataset: {uploaded_file.name}")
                except Exception as e:
                    st.error(f"Gagal membaca CSV: {e}")
        elif data_source == "Benchmark 2: Klasifikasi Gizi ('nilai-gizi')":
            if st.button("Terapkan Dataset Nilai Gizi"):
                st.session_state.dataset = generate_nutrition_dataset(n_samples=2000, random_seed=42)
                st.session_state.dataset_name = "Nilai_Gizi_Benchmark"
                st.session_state.target_col = "status_gizi"
                st.session_state.pipeline_run = False
                st.success("Dataset dialihkan ke Benchmark Nilai Gizi.")
        else:
            if st.button("Reset ke Benchmark Credit Card"):
                st.session_state.dataset = generate_credit_default_dataset(n_samples=2500, random_seed=42)
                st.session_state.dataset_name = "Credit Card Default Benchmark"
                st.session_state.target_col = "default"
                st.session_state.pipeline_run = False
                st.success("Dataset di-reset ke benchmark Credit Card.")

        target_candidates = list(st.session_state.dataset.columns)
        default_idx = target_candidates.index(st.session_state.target_col) if st.session_state.target_col in target_candidates else len(target_candidates) - 1
        st.session_state.target_col = st.selectbox("Pilih Kolom Target (Y):", target_candidates, index=default_idx)

    with col2:
        st.subheader("Hasil Pemeriksaan & Validasi Struktur Tabular")
        try:
            inspection = DatasetValidator.inspect(st.session_state.dataset, st.session_state.target_col)
            m1, m2, m3, m4 = st.columns(4)
            m1.metric("Total Sampel", f"{inspection['n_rows']:,}")
            m2.metric("Total Fitur", inspection['n_features'])
            m3.metric("Fitur Numerik", len(inspection['numeric_features']))
            m4.metric("Fitur Kategorikal", len(inspection['categorical_features']))

            st.markdown(f"**Distribusi Target:** `{inspection['class_distribution']}` | **Missing Values:** `{inspection['total_missing_values']}`")
            if inspection.get("is_severely_imbalanced"):
                st.warning("⚠️ Peringatan: Ketimpangan kelas terdeteksi (minoritas < 5%). Pertimbangkan metrik F1-score macro.")

            st.dataframe(st.session_state.dataset.head(6), width='stretch')
        except Exception as e:
            st.error(f"Validasi Gagal: {e}")


# ==========================================
# PAGE 2: EXPERIMENT CONFIGURATION
# ==========================================
elif page == "2. Experiment Configuration":
    st.markdown("<div class='main-header'>Halaman 2 — Konfigurasi Eksperimen & Model</div>", unsafe_allow_html=True)
    st.markdown("<div class='sub-header'>Atur parameter partisi bebas data leakage, model baseline (internal atau eksternal .pkl), dan seed.</div>", unsafe_allow_html=True)

    c1, c2 = st.columns(2)
    with c1:
        st.subheader("Parameter Pemisahan & Reproduksibilitas")
        exp_name = st.text_input("Nama Eksperimen", value="Stress_Test_Evaluation_v1")
        test_size = st.slider("Proporsi Test Set (Baseline & Shift Target)", min_value=0.10, max_value=0.40, value=0.20, step=0.05)
        seed = st.number_input("Fixed Random Seed", value=42, step=1)
        cv_folds = st.selectbox("Jumlah Fold Cross-Validation (Pelatihan Baseline)", [3, 5, 10], index=1)
        multiplier = st.slider("Multiplier Ambang Batas Dinamis (x Std Dev CV)", min_value=1.0, max_value=3.0, value=1.5, step=0.1)

    with c2:
        st.subheader("Pilihan Model Klasifikasi")
        model_mode = st.radio(
            "Mode Model:",
            ["Latih Model Baseline Standar (LogReg & XGBoost)", "Gunakan Model Pre-trained Pengguna (.pkl / .joblib)"]
        )

        use_lr = True
        use_xgb = True
        ext_path = None

        if model_mode == "Gunakan Model Pre-trained Pengguna (.pkl / .joblib)":
            uploaded_model = st.file_uploader("Unggah File Model (.pkl / .joblib)", type=["pkl", "joblib"])
            if uploaded_model is not None:
                # Save temp model file
                tfile = tempfile.NamedTemporaryFile(delete=False, suffix=".pkl")
                tfile.write(uploaded_model.read())
                tfile.flush()
                ext_path = tfile.name
                st.session_state.external_model_path = ext_path
                st.success(f"Model eksternal siap diuji: {uploaded_model.name}")
        else:
            st.session_state.external_model_path = None
            use_lr = st.checkbox("Logistic Regression (Model Linier)", value=True)
            use_xgb = st.checkbox("XGBoost (Model Non-Linier Berbasis Pohon)", value=True)

        st.markdown("""
        > **Pencegahan Data Leakage Konseptual:**
        > Scaling (StandardScaler) dan Encoding (OneHotEncoder) di-fit **hanya** pada Training Data.
        > Jarak statistik dihitung secara murni unsupervised tanpa Target Encoding.
        """)

    algos = []
    if use_lr: algos.append(ModelAlgorithm.LOGISTIC_REGRESSION)
    if use_xgb: algos.append(ModelAlgorithm.XGBOOST)

    if st.button("Simpan Konfigurasi Eksperimen", type="primary"):
        st.session_state.config = ExperimentConfig(
            dataset_name=st.session_state.dataset_name,
            target_column=st.session_state.target_col,
            test_size=test_size,
            random_seed=seed,
            algorithms=algos,
            cv_folds=cv_folds,
            threshold_std_multiplier=multiplier,
            external_model_path=st.session_state.external_model_path
        )
        st.success("Konfigurasi eksperimen berhasil disimpan!")


# ==========================================
# PAGE 3: SHIFT CONFIGURATION
# ==========================================
elif page == "3. Shift Configuration":
    st.markdown("<div class='main-header'>Halaman 3 — Konfigurasi Skenario Pergeseran Distribusi</div>", unsafe_allow_html=True)
    st.markdown("<div class='sub-header'>Bentuk pergeseran distribusi terkontrol dan periksa validitas asumsi serta overlap support.</div>", unsafe_allow_html=True)

    st.subheader("1. Pemilihan Shift Type")
    t1, t2, t3 = st.columns(3)
    with t1:
        st.markdown("<span class='badge-active'>AKTIF — Fokus Utama</span>", unsafe_allow_html=True)
        st.markdown("**Covariate Shift**\n\n$P(X)$ berubah, $P(Y|X)$ diasumsikan konstan.")
    with t2:
        st.markdown("<span class='badge-disabled'>NON-AKTIF — Rencana Pengembangan</span>", unsafe_allow_html=True)
        st.markdown("**Label Shift**\n\n$P(Y)$ berubah, $P(X|Y)$ konstan.")
    with t3:
        st.markdown("<span class='badge-disabled'>NON-AKTIF — Rencana Pengembangan</span>", unsafe_allow_html=True)
        st.markdown("**Concept Shift**\n\n$P(Y|X)$ berubah.")

    st.markdown("---")
    st.subheader("2. Pilihan Skenario Covariate Shift & Tingkat Keparahan (Severity)")

    sc1, sc2 = st.columns([1, 1])
    with sc1:
        scenario_choice = st.selectbox(
            "Pilih Skenario Pergeseran:",
            [
                "Skenario A: Feature Location Shift (Mean Shift)",
                "Skenario B: Feature Scale/Variance Shift (Variance Shift)",
                "Skenario C: Feature Shape / Outlier Ingestion (Extreme Shift)",
                "Skenario D: Categorical Proportion Shift (Frekuensi Kategori)",
                "Skenario E: Pure Covariate Shift via Biased Selection (P(Y|X) Konstan)"
            ]
        )
        severity_choice = st.select_slider(
            "Tingkat Keparahan (Severity):",
            options=[0.25, 0.50, 1.00],
            value=0.50,
            format_func=lambda x: f"{x} (Rendah)" if x == 0.25 else (f"{x} (Sedang)" if x == 0.50 else f"{x} (Tinggi)")
        )

        numeric_cols = [c for c in st.session_state.dataset.columns if pd.api.types.is_numeric_dtype(st.session_state.dataset[c]) and c != st.session_state.target_col]
        preview_feature = st.selectbox("Pilih Fitur untuk Pratinjau Distribusi:", numeric_cols)

    with sc2:
        st.subheader("Pratinjau Distribusi & Pemeriksaan Dukungan (Support Overlap)")
        gen = ShiftGenerator(random_seed=42)

        if "Location" in scenario_choice: scen_enum = ShiftScenario.MEAN_SHIFT
        elif "Variance" in scenario_choice: scen_enum = ShiftScenario.VARIANCE_SHIFT
        elif "Outlier" in scenario_choice: scen_enum = ShiftScenario.OUTLIER_SHIFT
        elif "Categorical" in scenario_choice: scen_enum = ShiftScenario.CATEGORICAL_SHIFT
        else: scen_enum = ShiftScenario.SUBSAMPLING_SHIFT

        sev_enum = ShiftSeverity(severity_choice)

        try:
            shifted_df, meta = gen.apply_shift(
                st.session_state.dataset,
                scenario=scen_enum,
                severity=sev_enum,
                target_features=[preview_feature] if scen_enum != ShiftScenario.CATEGORICAL_SHIFT else None
            )

            st.markdown(f"**Tipe Validitas Shift:** `{meta.get('shift_validity_type')}`")
            st.markdown(f"**Dukungan Overlap (Support Positivity):** `{meta.get('support_overlap_pct')}%`")

            fig, ax = plt.subplots(figsize=(6, 3))
            sns.kdeplot(st.session_state.dataset[preview_feature], label="Baseline", color="#0284C7", fill=True, alpha=0.3, ax=ax)
            sns.kdeplot(shifted_df[preview_feature], label=f"Shifted ({sev_enum.value})", color="#DC2626", fill=True, alpha=0.3, ax=ax)
            ax.set_title(f"Distribusi Fitur: {preview_feature}", fontsize=10, fontweight="bold")
            ax.legend()
            st.pyplot(fig)
            plt.close()
        except Exception as e:
            st.info(f"Pratinjau skenario: {e}")


# ==========================================
# PAGE 4: DISTRIBUTION DISTANCE
# ==========================================
elif page == "4. Distribution Distance":
    st.markdown("<div class='main-header'>Halaman 4 — Pengukuran Jarak Statistik Distribusi Data</div>", unsafe_allow_html=True)
    st.markdown("<div class='sub-header'>Menghitung Maximum Mean Discrepancy (MMD) dan Wasserstein Distance secara empiris.</div>", unsafe_allow_html=True)

    st.markdown("""
    | Metode Distance | Formulasi Teoretis & Kernel | Mitigasi Skalabilitas |
    |---|---|---|
    | **Maximum Mean Discrepancy (MMD)** | Estimator unbiased $U$-statistic dengan RBF Gaussian Kernel dan Bandwidth Median Heuristic | Subsampling $N=5{,}000$ untuk mencegah kehabisan memori (*OOM*) |
    | **Wasserstein Distance** | Sliced Wasserstein Distance (SWD) dengan $L=50$ proyeksi acak seragam pada bola unit multidimensi | Kompleksitas $O(L \cdot N \log N)$ yang sangat efisien dan robust |
    """)

    if not st.session_state.pipeline_run:
        st.warning("⚠️ Eksperimen belum dijalankan. Jalankan eksekusi lengkap pada Halaman 5 atau Halaman 6 untuk melihat kalkulasi jarak.")
    else:
        df_res = st.session_state.profile.profile_df
        st.subheader("Matriks Jarak Distribusi Hasil Eksperimen")
        dist_view = df_res[["shift_scenario", "severity", "support_overlap_pct", "mmd_distance", "wasserstein_distance"]].drop_duplicates()
        st.dataframe(dist_view, width='stretch')

        fig, ax = plt.subplots(figsize=(8, 3.5))
        sns.barplot(data=dist_view, x="shift_scenario", y="mmd_distance", hue="severity", palette="Blues", ax=ax)
        ax.set_title("MMD Distance pada Berbagai Skenario & Severity", fontweight="bold")
        st.pyplot(fig)
        plt.close()


# ==========================================
# PAGE 5: MODEL EVALUATION
# ==========================================
elif page == "5. Model Evaluation":
    st.markdown("<div class='main-header'>Halaman 5 — Evaluasi Model & Degradasi Performa</div>", unsafe_allow_html=True)
    st.markdown("<div class='sub-header'>Pelatihan baseline (5-Fold CV / External Model) dan evaluasi respons model pada data shifted.</div>", unsafe_allow_html=True)

    if st.button("🚀 Jalankan Stress Testing Lengkap Sekarang", type="primary"):
        with st.spinner("Menjalankan pipeline eksperimen kuantitatif (Pre-processing, 5 Skenario, MMD, Wasserstein, Threshold Check)..."):
            cfg = getattr(st.session_state, "config", ExperimentConfig(
                dataset_name=st.session_state.dataset_name,
                target_column=st.session_state.target_col,
                random_seed=42,
                scenarios=[
                    ShiftScenario.MEAN_SHIFT,
                    ShiftScenario.VARIANCE_SHIFT,
                    ShiftScenario.OUTLIER_SHIFT,
                    ShiftScenario.CATEGORICAL_SHIFT,
                    ShiftScenario.SUBSAMPLING_SHIFT,
                ],
                external_model_path=st.session_state.external_model_path
            ))
            pipe = StressTestingPipeline(config=cfg)
            profile = pipe.run(df=st.session_state.dataset)

            st.session_state.pipeline = pipe
            st.session_state.profile = profile
            st.session_state.pipeline_run = True
            st.success("Eksperimen Stress Testing Berhasil Diselesaikan!")

    if st.session_state.pipeline_run:
        profile = st.session_state.profile
        st.subheader("1. Performa Baseline & Hasil 5-Fold Cross Validation")
        b_cols = st.columns(len(profile.baselines))
        for i, (m_name, base_eval) in enumerate(profile.baselines.items()):
            with b_cols[i]:
                st.markdown(f"**Model: `{m_name}`**")
                st.markdown(f"- Test F1: **{base_eval.test_metrics.f1_score}** | ROC-AUC: **{base_eval.test_metrics.roc_auc}** | Acc: **{base_eval.test_metrics.accuracy}**")
                st.markdown(f"- 5-Fold CV F1: **{base_eval.cv_scores_mean.get('f1_score')} ± {base_eval.cv_scores_std.get('f1_score')}**")
                thresh_val = round(1.5 * max(base_eval.cv_scores_std.get("f1_score", 0.01), 0.01), 4)
                st.markdown(f"- Batas Toleransi F1 (1.5x CV Std): **`{thresh_val}`**")

        st.markdown("---")
        st.subheader("2. Tabel Metrik & Degradasi Performa pada Shifted Data")
        cols_to_show = ["model", "shift_scenario", "severity", "support_overlap_pct", "baseline_f1", "shifted_f1", "abs_deg_f1", "rel_deg_f1_pct", "threshold_status"]
        st.dataframe(profile.profile_df[cols_to_show], width='stretch')


# ==========================================
# PAGE 6: RELIABILITY ANALYSIS
# ==========================================
elif page == "6. Reliability Analysis":
    st.markdown("<div class='main-header'>Halaman 6 — Analisis Ketahanan (*Reliability Analysis*)</div>", unsafe_allow_html=True)
    st.markdown("<div class='sub-header'>Pemeriksaan pelanggaran ambang batas dan korelasi Spearman Rank (Dekomposisi Global & Per-Skenario).</div>", unsafe_allow_html=True)

    if not st.session_state.pipeline_run:
        st.warning("⚠️ Jalankan eksperimen terlebih dahulu pada Halaman 5.")
    else:
        profile = st.session_state.profile
        df_res = profile.profile_df

        st.subheader("1. Ringkasan Status Ambang Batas Keandalan (Dynamic Threshold)")
        total_tests = len(df_res)
        breached = int(df_res["is_breached"].sum())
        safe = total_tests - breached

        k1, k2, k3 = st.columns(3)
        k1.metric("Total Kondisi Pengujian", total_tests)
        k2.metric("Dalam Batas Toleransi (Aman)", f"{safe} ({safe/total_tests*100:.1f}%)")
        k3.metric("Melewati Ambang Batas (Gagal)", f"{breached} ({breached/total_tests*100:.1f}%)")

        st.markdown("---")
        st.subheader("2. Analisis Korelasi Spearman Rank (Distance vs Degradasi F1)")
        corrs = profile.compute_correlations()
        c_cols = st.columns(len(corrs))
        for idx, (m_name, c) in enumerate(corrs.items()):
            with c_cols[idx]:
                st.markdown(f"**Model `{m_name}`**")
                ov = c.get("overall", {})
                m_f1 = ov.get("mmd_vs_f1_deg", {})
                w_f1 = ov.get("wasserstein_vs_f1_deg", {})
                st.write(f"• **MMD vs F1 (Overall):** $r_s = {m_f1.get('spearman_rho')}$ ($p = {m_f1.get('p_value')}$, sig={m_f1.get('is_significant')})")
                st.write(f"• **Wasserstein vs F1 (Overall):** $r_s = {w_f1.get('spearman_rho')}$ ($p = {w_f1.get('p_value')}$, sig={w_f1.get('is_significant')})")

                if m_f1.get("warning"):
                    st.caption(f"ℹ️ {m_f1.get('warning')}")

                # Per-scenario breakdown display
                per_scen = c.get("per_scenario", {})
                if per_scen:
                    st.markdown("**Korelasi Per-Skenario:**")
                    for s_k, s_v in per_scen.items():
                        st.caption(f"- `{s_k}`: MMD $r_s={s_v.get('mmd_vs_f1', {}).get('spearman_rho')}$, Wass $r_s={s_v.get('wasserstein_vs_f1', {}).get('spearman_rho')}$")

        st.markdown("---")
        st.subheader("3. Visualisasi Reliability Profile")
        g1, g2 = st.columns(2)
        with g1:
            fig1, ax1 = plt.subplots(figsize=(6, 4))
            sns.lineplot(data=df_res, x="severity", y="shifted_f1", hue="model", style="shift_scenario", markers=True, ax=ax1)
            ax1.set_title("F1-Score vs Shift Severity", fontweight="bold")
            st.pyplot(fig1)
            plt.close()

        with g2:
            fig2, ax2 = plt.subplots(figsize=(6, 4))
            sns.scatterplot(data=df_res, x="mmd_distance", y="abs_deg_f1", hue="model", style="shift_scenario", s=90, ax=ax2)
            sns.regplot(data=df_res, x="mmd_distance", y="abs_deg_f1", scatter=False, ax=ax2, color="gray", line_kws={"linestyle": "--"})
            ax2.set_title("MMD Distance vs Degradasi F1", fontweight="bold")
            st.pyplot(fig2)
            plt.close()


# ==========================================
# PAGE 7: HISTORY & EXPORT
# ==========================================
elif page == "7. History & Export":
    st.markdown("<div class='main-header'>Halaman 7 — Riwayat Eksperimen & Ekspor Artefak</div>", unsafe_allow_html=True)
    st.markdown("<div class='sub-header'>Unduh tabel lengkap Reliability Profile (Schema 1.1.0), konfigurasi JSON, dan laporan ringkasan riset.</div>", unsafe_allow_html=True)

    if not st.session_state.pipeline_run:
        st.warning("⚠️ Belum ada hasil eksperimen yang tersimpan untuk diekspor. Silakan jalankan eksperimen terlebih dahulu.")
    else:
        profile = st.session_state.profile
        st.subheader(f"Ekspor Artefak Eksperimen (Schema Version `{profile.schema_version}`)")

        csv_data = profile.profile_df.to_csv(index=False).encode("utf-8")
        st.download_button(
            label="📥 Unduh Reliability Profile (CSV)",
            data=csv_data,
            file_name=f"reliability_profile_{profile.experiment_id}.csv",
            mime="text/csv"
        )

        json_dict = {
            "schema_version": profile.schema_version,
            "framework_version": profile.framework_version,
            "timestamp": profile.timestamp,
            "experiment_id": profile.experiment_id,
            "dataset_name": profile.dataset_name,
            "correlations": profile.compute_correlations(),
            "records": profile.profile_df.to_dict(orient="records")
        }
        json_data = json.dumps(json_dict, indent=2).encode("utf-8")
        st.download_button(
            label="📥 Unduh Hasil Eksperimen Lengkap (JSON)",
            data=json_data,
            file_name=f"experiment_results_{profile.experiment_id}.json",
            mime="application/json"
        )

        md_report = profile.generate_markdown_summary()
        st.download_button(
            label="📥 Unduh Laporan Eksekutif (Markdown)",
            data=md_report.encode("utf-8"),
            file_name=f"executive_summary_{profile.experiment_id}.md",
            mime="text/markdown"
        )

        st.markdown("### Pratinjau Laporan Ringkasan")
        st.markdown(md_report)
