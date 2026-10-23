"""
Headless Experiment Runner for ML Reliability Stress Testing.
Executes standard full benchmark on Credit Card Default dataset,
generates Reliability Profile, computes Spearman Rank Correlation,
exports CSV, JSON, and saves publication-quality plots to output/.
"""

import os
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt
import seaborn as sns
import pandas as pd
import numpy as np

from framework.config import (
    ExperimentConfig,
    ShiftScenario,
    ShiftSeverity,
    ModelAlgorithm
)
from framework.pipeline import StressTestingPipeline
from framework.dataset.loader import generate_credit_default_dataset


def run_full_benchmark(output_dir: str = "output"):
    os.makedirs(output_dir, exist_ok=True)
    print("=" * 70)
    print("MEMULAI EKSPERIMEN STRESS TESTING ML RELIABILITY TERKONTROL")
    print("=" * 70)

    # 1. Konfigurasi Eksperimen
    config = ExperimentConfig(
        dataset_name="Credit_Card_Default_Benchmark",
        target_column="default",
        test_size=0.20,
        random_seed=42,
        algorithms=[ModelAlgorithm.LOGISTIC_REGRESSION, ModelAlgorithm.XGBOOST],
        scenarios=[
            ShiftScenario.MEAN_SHIFT,
            ShiftScenario.VARIANCE_SHIFT,
            ShiftScenario.OUTLIER_SHIFT
        ],
        severities=[
            ShiftSeverity.LOW,      # 0.25
            ShiftSeverity.MEDIUM,   # 0.50
            ShiftSeverity.HIGH      # 1.00
        ],
        cv_folds=5,
        threshold_std_multiplier=1.5,
        mmd_subsample_limit=5000
    )

    print(f"Dataset: {config.dataset_name} (Fixed Random Seed = {config.random_seed})")
    print(f"Algoritma: {[a.value for a in config.algorithms]}")
    print(f"Skenario Shift: {[s.value for s in config.scenarios]}")
    print(f"Tingkat Severity: {[sev.value for sev in config.severities]}")
    print(f"Batas Keandalan Dinamis: Penurunan > {config.threshold_std_multiplier} x Std Dev CV (5-Fold)")
    print("-" * 70)

    # 2. Inisialisasi & Eksekusi Pipeline
    df = generate_credit_default_dataset(n_samples=3000, random_seed=config.random_seed)
    pipeline = StressTestingPipeline(config=config)
    profile = pipeline.run(df=df)

    # 3. Cetak Hasil Baseline
    print("\n[HASIL PELATIHAN BASELINE & 5-FOLD CV]")
    for m_name, base_eval in profile.baselines.items():
        print(f"\nModel: {m_name}")
        print(f"  Test Metrics  -> F1: {base_eval.test_metrics.f1_score:.4f} | ROC-AUC: {base_eval.test_metrics.roc_auc:.4f} | Acc: {base_eval.test_metrics.accuracy:.4f}")
        print(f"  5-Fold CV F1  -> Mean: {base_eval.cv_scores_mean.get('f1_score', 0):.4f} ± {base_eval.cv_scores_std.get('f1_score', 0):.4f}")
        thresh_val = round(config.threshold_std_multiplier * max(base_eval.cv_scores_std.get('f1_score', 0.01), 0.01), 4)
        print(f"  Batas Toleransi F1 (1.5x CV Std Dev) = {thresh_val}")

    # 4. Ekspor Data
    csv_file = os.path.join(output_dir, "reliability_profile.csv")
    json_file = os.path.join(output_dir, "reliability_profile.json")
    md_file = os.path.join(output_dir, "executive_summary.md")

    profile.to_csv(csv_file)
    profile.to_json(json_file)
    with open(md_file, "w", encoding="utf-8") as f:
        f.write(profile.generate_markdown_summary())

    print(f"\n[ARTEFAK DATA TERSIMPAN]")
    print(f"  - CSV : {csv_file}")
    print(f"  - JSON: {json_file}")
    print(f"  - MD  : {md_file}")

    # 5. Visualisasi Publikasi
    generate_figures(profile, output_dir)

    # 6. Analisis Korelasi Spearman Rank
    corrs = profile.compute_correlations()
    print("\n[ANALISIS KORELASI SPEARMAN RANK (DISTANCE vs F1 DEGRADATION)]")
    for m_name, c in corrs.items():
        m_f1 = c["mmd_vs_f1_deg"]
        w_f1 = c["wasserstein_vs_f1_deg"]
        print(f"Model {m_name}:")
        print(f"  - MMD vs F1 Degradation        : rs = {m_f1.get('spearman_rho')} (p-value = {m_f1.get('p_value')})")
        print(f"  - Wasserstein vs F1 Degradation: rs = {w_f1.get('spearman_rho')} (p-value = {w_f1.get('p_value')})")

    total_tests = len(profile.profile_df)
    breached = int(profile.profile_df["is_breached"].sum())
    print("\n[RINGKASAN STATUS THRESHOLD]")
    print(f"  - Total Kondisi Shift Dievaluasi : {total_tests}")
    print(f"  - Melewati Batas Toleransi (Gagal): {breached} ({breached/total_tests*100:.1f}%)")
    print(f"  - Dalam Batas Toleransi (Aman)   : {total_tests - breached} ({(total_tests - breached)/total_tests*100:.1f}%)")
    print("=" * 70)


def generate_figures(profile: StressTestingPipeline, output_dir: str):
    df_res = profile.profile_df.copy()
    sns.set_theme(style="whitegrid", font="sans-serif")

    # Ambil batas toleransi baseline rata-rata untuk panduan visual
    base_lr = profile.baselines["logistic_regression"]
    base_xgb = profile.baselines["xgboost"]
    lr_thresh_f1 = base_lr.test_metrics.f1_score - (1.5 * max(base_lr.cv_scores_std.get("f1_score", 0.01), 0.01))
    xgb_thresh_f1 = base_xgb.test_metrics.f1_score - (1.5 * max(base_xgb.cv_scores_std.get("f1_score", 0.01), 0.01))

    # =========================================================================
    # FIGUR 1: Respons Model terhadap Setiap Skenario (3 Subplot yang Jelas)
    # =========================================================================
    fig, axes = plt.subplots(1, 3, figsize=(16, 5), sharey=True)
    scenarios = [
        ("mean_shift", "Skenario A: Perubahan Lokasi (Mean Shift)", axes[0]),
        ("variance_shift", "Skenario B: Perubahan Sebaran (Variance Shift)", axes[1]),
        ("outlier_shift", "Skenario C: Injeksi Ekstrem (Outlier Shift)", axes[2])
    ]

    model_colors = {"logistic_regression": "#1E40AF", "xgboost": "#047857"}
    model_labels = {"logistic_regression": "Logistic Regression (Linier)", "xgboost": "XGBoost (Pohon Non-Linier)"}

    for scen_key, title, ax in scenarios:
        sub = df_res[df_res["shift_scenario"] == scen_key]
        for m_name in ["logistic_regression", "xgboost"]:
            m_sub = sub[sub["model"] == m_name]
            ax.plot(
                m_sub["severity"],
                m_sub["shifted_f1"],
                marker="o",
                markersize=8,
                linewidth=2.5,
                color=model_colors[m_name],
                label=model_labels[m_name]
            )

        # Garis Batas Bahaya / Gagal (Threshold)
        ax.axhline(lr_thresh_f1, color="#DC2626", linestyle=":", linewidth=1.8, label="Batas Kritis LogReg (1.5x CV Std)")
        ax.axhline(xgb_thresh_f1, color="#B91C1C", linestyle="--", linewidth=1.8, label="Batas Kritis XGBoost (1.5x CV Std)")

        ax.set_title(title, fontsize=11, fontweight="bold", pad=10)
        ax.set_xlabel("Tingkat Keparahan / Severity\n(0.25=Rendah, 0.50=Sedang, 1.00=Tinggi)", fontsize=10)
        ax.set_xticks([0.25, 0.50, 1.00])
        ax.set_xticklabels(["0.25 (Rendah)", "0.50 (Sedang)", "1.00 (Tinggi)"])
        ax.grid(True, linestyle="--", alpha=0.6)

    axes[0].set_ylabel("Skor Performa F1-Score (Semakin Tinggi Semakin Baik)", fontsize=11)
    # Hanya pasang legend di panel pertama agar tidak berantakan
    axes[0].legend(loc="lower left", fontsize=8, frameon=True, facecolor="white", edgecolor="#CBD5E1")

    fig.suptitle("Respons Ketahanan Model terhadap Perubahan Distribusi Data Terkontrol", fontsize=14, fontweight="bold", y=1.03)
    plt.tight_layout()
    fig1_path = os.path.join(output_dir, "severity_vs_performance.png")
    plt.savefig(fig1_path, dpi=300, bbox_inches="tight")
    plt.close()

    # =========================================================================
    # FIGUR 2: Jarak Distribusi vs Degradasi Performa (Mudah Dibaca)
    # =========================================================================
    fig, (ax1, ax2) = plt.subplots(1, 2, figsize=(15, 5))

    # Panel Kiri: MMD vs Degradasi
    for m_name in ["logistic_regression", "xgboost"]:
        m_df = df_res[df_res["model"] == m_name]
        ax1.scatter(
            m_df["mmd_distance"],
            m_df["abs_deg_f1"],
            color=model_colors[m_name],
            s=80,
            alpha=0.85,
            label=model_labels[m_name]
        )
        sns.regplot(
            data=m_df,
            x="mmd_distance",
            y="abs_deg_f1",
            ax=ax1,
            color=model_colors[m_name],
            scatter=False,
            line_kws={"linestyle": "--", "alpha": 0.7, "linewidth": 2}
        )

    ax1.set_title("Maximum Mean Discrepancy (MMD) vs Penurunan F1", fontsize=12, fontweight="bold")
    ax1.set_xlabel("Nilai Jarak MMD (Semakin ke Kanan = Data Semakin Berbeda)", fontsize=10)
    ax1.set_ylabel("Degradasi Penurunan F1-Score (Semakin ke Atas = Model Makin Rusak)", fontsize=10)
    ax1.legend(loc="upper left", fontsize=9)
    ax1.grid(True, linestyle="--", alpha=0.5)

    # Panel Kanan: Wasserstein vs Degradasi
    for m_name in ["logistic_regression", "xgboost"]:
        m_df = df_res[df_res["model"] == m_name]
        ax2.scatter(
            m_df["wasserstein_distance"],
            m_df["abs_deg_f1"],
            color=model_colors[m_name],
            s=80,
            alpha=0.85,
            label=model_labels[m_name]
        )
        sns.regplot(
            data=m_df,
            x="wasserstein_distance",
            y="abs_deg_f1",
            ax=ax2,
            color=model_colors[m_name],
            scatter=False,
            line_kws={"linestyle": "--", "alpha": 0.7, "linewidth": 2}
        )

    ax2.set_title("Wasserstein Distance vs Penurunan F1", fontsize=12, fontweight="bold")
    ax2.set_xlabel("Nilai Jarak Wasserstein (Semakin ke Kanan = Data Semakin Berbeda)", fontsize=10)
    ax2.set_ylabel("Degradasi Penurunan F1-Score (Semakin ke Atas = Model Makin Rusak)", fontsize=10)
    ax2.legend(loc="upper left", fontsize=9)
    ax2.grid(True, linestyle="--", alpha=0.5)

    plt.tight_layout()
    fig2_path = os.path.join(output_dir, "distance_vs_degradation.png")
    plt.savefig(fig2_path, dpi=300, bbox_inches="tight")
    plt.close()

    # =========================================================================
    # FIGUR 3: Peta Status Keandalan (Hijau = Aman, Merah = Gagal / Berisiko)
    # =========================================================================
    plt.figure(figsize=(9, 4.5))
    pivot_table = df_res.pivot_table(
        index=["model", "shift_scenario"],
        columns="severity",
        values="is_breached",
        aggfunc="max"
    )
    # Ganti index agar mudah dibaca manusia
    pivot_table.index = [f"{m.upper()} — {s.replace('_', ' ').title()}" for m, s in pivot_table.index]
    pivot_table.columns = ["Rendah (0.25)", "Sedang (0.50)", "Tinggi (1.00)"]

    # Heatmap custom: 0 = Hijau (#DCFCE7), 1 = Merah (#FCA5A5)
    cmap = sns.color_palette(["#86EFAC", "#F87171"])
    sns.heatmap(
        pivot_table,
        annot=True,
        cmap=cmap,
        cbar=False,
        linewidths=2,
        linecolor="white",
        fmt="d"
    )
    plt.title("Peta Status Keandalan Model (0 = AMAN / Toleransi, 1 = GAGAL / Melewati Batas)", fontsize=12, fontweight="bold", pad=12)
    plt.xlabel("Tingkat Keparahan Pergeseran (Shift Severity)", fontsize=11)
    plt.ylabel("Kombinasi Model & Skenario Pergeseran", fontsize=11)
    plt.tight_layout()
    fig3_path = os.path.join(output_dir, "threshold_breach_matrix.png")
    plt.savefig(fig3_path, dpi=300, bbox_inches="tight")
    plt.close()

    print(f"  - Grafik 1 (Kurva Skenario Rapi): {fig1_path}")
    print(f"  - Grafik 2 (Distance vs Degradasi Bersih): {fig2_path}")
    print(f"  - Grafik 3 (Peta Status Hijau/Merah): {fig3_path}")


if __name__ == "__main__":
    run_full_benchmark()
