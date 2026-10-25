"""
Headless Experiment Runner for ML Reliability Stress Testing (Schema Version 1.1.0).
Executes standard full benchmark with all 5 Covariate Shift scenarios
(Mean, Variance, Outlier, Categorical, Subsampling) on Credit Card Default dataset,
generates versioned Reliability Profile, computes Spearman Rank Correlation with p-values,
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
    ModelAlgorithm,
    SCHEMA_VERSION
)
from framework.pipeline import StressTestingPipeline
from framework.dataset.loader import generate_credit_default_dataset


def run_full_benchmark(output_dir: str = "output"):
    os.makedirs(output_dir, exist_ok=True)
    print("=" * 70)
    print("MEMULAI EKSPERIMEN STRESS TESTING ML RELIABILITY TERKONTROL (v1.1.0)")
    print("=" * 70)

    # 1. Konfigurasi Eksperimen Komprehensif (Semua 5 Skenario)
    config = ExperimentConfig(
        dataset_name="Credit_Card_Default_Benchmark",
        target_column="default",
        test_size=0.20,
        random_seed=42,
        algorithms=[ModelAlgorithm.LOGISTIC_REGRESSION, ModelAlgorithm.XGBOOST],
        scenarios=[
            ShiftScenario.MEAN_SHIFT,          # Skenario A
            ShiftScenario.VARIANCE_SHIFT,      # Skenario B
            ShiftScenario.OUTLIER_SHIFT,        # Skenario C
            ShiftScenario.CATEGORICAL_SHIFT,   # Skenario D
            ShiftScenario.SUBSAMPLING_SHIFT,   # Skenario E (Pure Covariate Shift)
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

    # 4. Ekspor Data (Schema 1.1.0)
    csv_file = os.path.join(output_dir, "reliability_profile.csv")
    json_file = os.path.join(output_dir, "reliability_profile.json")
    md_file = os.path.join(output_dir, "executive_summary.md")

    profile.to_csv(csv_file)
    profile.to_json(json_file)
    with open(md_file, "w", encoding="utf-8") as f:
        f.write(profile.generate_markdown_summary())

    print(f"\n[ARTEFAK DATA TERSIMPAN (Schema {SCHEMA_VERSION})]")
    print(f"  - CSV : {csv_file}")
    print(f"  - JSON: {json_file}")
    print(f"  - MD  : {md_file}")

    # 5. Visualisasi Publikasi
    generate_figures(profile, output_dir)

    # 6. Analisis Korelasi Spearman Rank (Overall & Per-Scenario)
    corrs = profile.compute_correlations()
    print("\n[ANALISIS KORELASI SPEARMAN RANK (DISTANCE vs F1 DEGRADATION)]")
    for m_name, c in corrs.items():
        ov = c.get("overall", {})
        m_f1 = ov.get("mmd_vs_f1_deg", {})
        w_f1 = ov.get("wasserstein_vs_f1_deg", {})
        print(f"\nModel {m_name} (Overall N={m_f1.get('n_samples')}):")
        print(f"  - MMD vs F1 Degradation        : rs = {m_f1.get('spearman_rho')} (p = {m_f1.get('p_value')}, sig={m_f1.get('is_significant')})")
        print(f"  - Wasserstein vs F1 Degradation: rs = {w_f1.get('spearman_rho')} (p = {w_f1.get('p_value')}, sig={w_f1.get('is_significant')})")

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

    base_lr = profile.baselines.get("logistic_regression")
    base_xgb = profile.baselines.get("xgboost")
    lr_thresh_f1 = base_lr.test_metrics.f1_score - (1.5 * max(base_lr.cv_scores_std.get("f1_score", 0.01), 0.01)) if base_lr else 0.5
    xgb_thresh_f1 = base_xgb.test_metrics.f1_score - (1.5 * max(base_xgb.cv_scores_std.get("f1_score", 0.01), 0.01)) if base_xgb else 0.5

    # =========================================================================
    # FIGUR 1: Respons Model terhadap Setiap Skenario
    # =========================================================================
    unique_scenarios = list(df_res["shift_scenario"].unique())
    n_scen = len(unique_scenarios)
    fig, axes = plt.subplots(1, n_scen, figsize=(4 * n_scen, 4.5), sharey=True)
    if n_scen == 1:
        axes = [axes]

    model_colors = {"logistic_regression": "#1E40AF", "xgboost": "#047857"}
    model_labels = {"logistic_regression": "Logistic Regression", "xgboost": "XGBoost"}

    for idx, scen_key in enumerate(unique_scenarios):
        ax = axes[idx]
        sub = df_res[df_res["shift_scenario"] == scen_key]
        for m_name in sub["model"].unique():
            m_sub = sub[sub["model"] == m_name]
            ax.plot(
                m_sub["severity"],
                m_sub["shifted_f1"],
                marker="o",
                markersize=7,
                linewidth=2.2,
                color=model_colors.get(m_name, "#64748B"),
                label=model_labels.get(m_name, m_name)
            )

        ax.axhline(lr_thresh_f1, color="#DC2626", linestyle=":", linewidth=1.5, label="Batas LogReg" if idx == 0 else "")
        ax.axhline(xgb_thresh_f1, color="#B91C1C", linestyle="--", linewidth=1.5, label="Batas XGBoost" if idx == 0 else "")

        title_clean = scen_key.replace("_", " ").title()
        ax.set_title(title_clean, fontsize=10, fontweight="bold", pad=8)
        ax.set_xlabel("Severity (0.25, 0.50, 1.00)", fontsize=9)
        ax.set_xticks([0.25, 0.50, 1.00])
        ax.grid(True, linestyle="--", alpha=0.5)

    axes[0].set_ylabel("F1-Score", fontsize=10)
    axes[0].legend(loc="lower left", fontsize=8, frameon=True, facecolor="white")

    fig.suptitle("Respons Ketahanan Model terhadap Skenario Pergeseran Terkontrol", fontsize=13, fontweight="bold", y=1.02)
    plt.tight_layout()
    fig1_path = os.path.join(output_dir, "severity_vs_performance.png")
    plt.savefig(fig1_path, dpi=300, bbox_inches="tight")
    plt.close()

    # =========================================================================
    # FIGUR 2: Jarak Distribusi vs Degradasi Performa
    # =========================================================================
    fig, (ax1, ax2) = plt.subplots(1, 2, figsize=(14, 4.8))

    for m_name in df_res["model"].unique():
        m_df = df_res[df_res["model"] == m_name]
        ax1.scatter(
            m_df["mmd_distance"],
            m_df["abs_deg_f1"],
            color=model_colors.get(m_name, "#64748B"),
            s=75,
            alpha=0.85,
            label=model_labels.get(m_name, m_name)
        )
        sns.regplot(
            data=m_df,
            x="mmd_distance",
            y="abs_deg_f1",
            ax=ax1,
            color=model_colors.get(m_name, "#64748B"),
            scatter=False,
            line_kws={"linestyle": "--", "alpha": 0.7, "linewidth": 1.8}
        )

    ax1.set_title("MMD Distance vs Penurunan F1", fontsize=11, fontweight="bold")
    ax1.set_xlabel("Maximum Mean Discrepancy (MMD)", fontsize=9)
    ax1.set_ylabel("Degradasi Absolut F1-Score", fontsize=9)
    ax1.legend(loc="upper left", fontsize=8)
    ax1.grid(True, linestyle="--", alpha=0.5)

    for m_name in df_res["model"].unique():
        m_df = df_res[df_res["model"] == m_name]
        ax2.scatter(
            m_df["wasserstein_distance"],
            m_df["abs_deg_f1"],
            color=model_colors.get(m_name, "#64748B"),
            s=75,
            alpha=0.85,
            label=model_labels.get(m_name, m_name)
        )
        sns.regplot(
            data=m_df,
            x="wasserstein_distance",
            y="abs_deg_f1",
            ax=ax2,
            color=model_colors.get(m_name, "#64748B"),
            scatter=False,
            line_kws={"linestyle": "--", "alpha": 0.7, "linewidth": 1.8}
        )

    ax2.set_title("Wasserstein Distance vs Penurunan F1", fontsize=11, fontweight="bold")
    ax2.set_xlabel("Sliced Wasserstein Distance", fontsize=9)
    ax2.set_ylabel("Degradasi Absolut F1-Score", fontsize=9)
    ax2.legend(loc="upper left", fontsize=8)
    ax2.grid(True, linestyle="--", alpha=0.5)

    plt.tight_layout()
    fig2_path = os.path.join(output_dir, "distance_vs_degradation.png")
    plt.savefig(fig2_path, dpi=300, bbox_inches="tight")
    plt.close()

    # =========================================================================
    # FIGUR 3: Peta Status Keandalan
    # =========================================================================
    plt.figure(figsize=(9, 5))
    pivot_table = df_res.pivot_table(
        index=["model", "shift_scenario"],
        columns="severity",
        values="is_breached",
        aggfunc="max"
    )
    pivot_table.index = [f"{m.upper()} — {s.replace('_', ' ').title()}" for m, s in pivot_table.index]
    pivot_table.columns = ["0.25 (Rendah)", "0.50 (Sedang)", "1.00 (Tinggi)"]

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
    plt.title("Peta Status Keandalan Model (0 = AMAN, 1 = MELEWATI AMBANG BATAS)", fontsize=11, fontweight="bold", pad=10)
    plt.xlabel("Shift Severity", fontsize=10)
    plt.ylabel("Model & Skenario Pergeseran", fontsize=10)
    plt.tight_layout()
    fig3_path = os.path.join(output_dir, "threshold_breach_matrix.png")
    plt.savefig(fig3_path, dpi=300, bbox_inches="tight")
    plt.close()

    print(f"  - Grafik 1 (Kurva Skenario Rapi): {fig1_path}")
    print(f"  - Grafik 2 (Distance vs Degradasi Bersih): {fig2_path}")
    print(f"  - Grafik 3 (Peta Status Hijau/Merah): {fig3_path}")


if __name__ == "__main__":
    run_full_benchmark()
