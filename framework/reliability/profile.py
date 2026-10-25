"""
Reliability Profile Generator and Report Builder (Schema Version 1.1.0).
Assembles the complete matrix of experimental conditions, distances,
performance metrics, degradations, threshold statuses, support overlap,
and per-scenario/overall Spearman rank correlations.
"""

from typing import List, Dict, Any, Optional
import json
from datetime import datetime, timezone
import pandas as pd
from ..config import (
    ShiftResult,
    BaselineEvaluation,
    ThresholdStatus,
    SCHEMA_VERSION,
    FRAMEWORK_VERSION,
    RunStatus
)
from .correlation import CorrelationAnalyzer


class ReliabilityProfile:
    """
    Constructs, queries, and exports the final ML Reliability Profile.
    Includes full experiment provenance, versioned schemas, and per-scenario decomposition.
    """

    def __init__(
        self,
        experiment_id: str,
        dataset_name: str,
        baselines: Dict[str, BaselineEvaluation],
        shift_results: List[ShiftResult],
        config_metadata: Optional[Dict[str, Any]] = None,
        dataset_metadata: Optional[Dict[str, Any]] = None
    ):
        self.schema_version = SCHEMA_VERSION
        self.framework_version = FRAMEWORK_VERSION
        self.timestamp = datetime.now(timezone.utc).isoformat()
        self.experiment_id = experiment_id
        self.dataset_name = dataset_name
        self.baselines = baselines
        self.shift_results = shift_results
        self.config_metadata = config_metadata or {}
        self.dataset_metadata = dataset_metadata or {}
        self.run_status = RunStatus.SUCCESS.value
        self.profile_df = self._build_dataframe()

    def _build_dataframe(self) -> pd.DataFrame:
        rows = []
        for res in self.shift_results:
            scenario_name = res.scenario.value
            severity_val = float(res.severity.value)
            mmd = res.mmd_distance
            wass = res.wasserstein_distance
            overlap = res.support_overlap_pct
            validity_type = res.shift_validity_type

            for model_name, shifted_m in res.shifted_metrics.items():
                base_eval = self.baselines[model_name]
                base_m = base_eval.test_metrics
                cv_f1_std = base_eval.cv_scores_std.get("f1_score", 0.0)

                abs_deg = res.degradation_absolute[model_name]
                rel_deg = res.degradation_relative_pct[model_name]
                status = res.threshold_status[model_name].value
                thresh_val = res.threshold_value[model_name]

                row = {
                    "schema_version": self.schema_version,
                    "experiment_id": self.experiment_id,
                    "dataset": self.dataset_name,
                    "model": model_name,
                    "shift_scenario": scenario_name,
                    "shift_validity_type": validity_type,
                    "severity": severity_val,
                    "support_overlap_pct": overlap,
                    "mmd_distance": round(mmd, 4),
                    "wasserstein_distance": round(wass, 4),
                    # Baseline Metrics
                    "baseline_f1": base_m.f1_score,
                    "baseline_roc_auc": base_m.roc_auc,
                    "baseline_accuracy": base_m.accuracy,
                    # Shifted Metrics
                    "shifted_f1": shifted_m.f1_score,
                    "shifted_roc_auc": shifted_m.roc_auc,
                    "shifted_accuracy": shifted_m.accuracy,
                    # Degradation
                    "abs_deg_f1": abs_deg.get("f1_score", 0.0),
                    "rel_deg_f1_pct": rel_deg.get("f1_score", 0.0),
                    "abs_deg_roc_auc": abs_deg.get("roc_auc", 0.0),
                    "abs_deg_accuracy": abs_deg.get("accuracy", 0.0),
                    # Threshold
                    "cv_f1_std": round(cv_f1_std, 4),
                    "threshold_value": thresh_val,
                    "threshold_value_1_5x": thresh_val,
                    "threshold_status": status,
                    "is_breached": 1 if status == ThresholdStatus.BREACHED.value else 0
                }
                rows.append(row)

        return pd.DataFrame(rows)

    def compute_correlations(self) -> Dict[str, Dict[str, Any]]:
        """
        Computes both overall and per-scenario Spearman rank correlation.
        """
        results = {}
        for model_name in self.baselines.keys():
            sub_df = self.profile_df[self.profile_df["model"] == model_name]
            if len(sub_df) < 3:
                continue

            mmd_vals = sub_df["mmd_distance"].tolist()
            wass_vals = sub_df["wasserstein_distance"].tolist()
            deg_f1 = sub_df["abs_deg_f1"].tolist()
            deg_auc = sub_df["abs_deg_roc_auc"].tolist()

            model_corr: Dict[str, Any] = {
                "overall": {
                    "mmd_vs_f1_deg": CorrelationAnalyzer.compute_spearman(mmd_vals, deg_f1),
                    "wasserstein_vs_f1_deg": CorrelationAnalyzer.compute_spearman(wass_vals, deg_f1),
                    "mmd_vs_auc_deg": CorrelationAnalyzer.compute_spearman(mmd_vals, deg_auc),
                    "wasserstein_vs_auc_deg": CorrelationAnalyzer.compute_spearman(wass_vals, deg_auc),
                },
                "per_scenario": {}
            }

            # Per-scenario correlation breakdown
            for scenario in sub_df["shift_scenario"].unique():
                scen_df = sub_df[sub_df["shift_scenario"] == scenario]
                if len(scen_df) >= 3:
                    s_mmd = scen_df["mmd_distance"].tolist()
                    s_wass = scen_df["wasserstein_distance"].tolist()
                    s_deg = scen_df["abs_deg_f1"].tolist()
                    model_corr["per_scenario"][str(scenario)] = {
                        "mmd_vs_f1": CorrelationAnalyzer.compute_spearman(s_mmd, s_deg),
                        "wasserstein_vs_f1": CorrelationAnalyzer.compute_spearman(s_wass, s_deg)
                    }

            results[model_name] = model_corr

        return results

    def to_csv(self, filepath: str) -> None:
        self.profile_df.to_csv(filepath, index=False)

    def to_json(self, filepath: str) -> None:
        data = {
            "schema_version": self.schema_version,
            "framework_version": self.framework_version,
            "timestamp": self.timestamp,
            "experiment_id": self.experiment_id,
            "dataset_name": self.dataset_name,
            "dataset_metadata": self.dataset_metadata,
            "config_metadata": self.config_metadata,
            "run_status": self.run_status,
            "correlations": self.compute_correlations(),
            "records": self.profile_df.to_dict(orient="records")
        }
        with open(filepath, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=2)

    def generate_markdown_summary(self) -> str:
        """
        Creates an executive summary in Markdown format meeting academic rigor standards.
        """
        corrs = self.compute_correlations()
        lines = [
            f"# Laporan Eksekutif Evaluasi Ketahanan Model (Reliability Profile)",
            f"- **Schema Version:** `{self.schema_version}`",
            f"- **Framework Version:** `{self.framework_version}`",
            f"- **Timestamp (UTC):** `{self.timestamp}`",
            f"- **Experiment ID:** `{self.experiment_id}`",
            f"- **Dataset:** `{self.dataset_name}`\n",
            "## 1. Performa Baseline & Ambang Batas Keandalan (5-Fold CV)",
        ]

        for model_name, base in self.baselines.items():
            lines.append(f"### Model: `{model_name}`")
            lines.append(f"- **Test Set Baseline:** F1-Score: **{base.test_metrics.f1_score}** | ROC-AUC: **{base.test_metrics.roc_auc}** | Accuracy: **{base.test_metrics.accuracy}**")
            lines.append(f"- **5-Fold Cross-Validation:** F1-Score Mean: **{base.cv_scores_mean.get('f1_score')} ± {base.cv_scores_std.get('f1_score')}**")
            thresh_val = round(1.5 * max(base.cv_scores_std.get('f1_score', 0.01), 0.01), 4)
            lines.append(f"- **Batas Toleransi Dinamis (1.5x Std Dev CV):** `Δ_abs <= {thresh_val}`")

        lines.append("\n## 2. Analisis Korelasi Spearman Rank (Distance vs Degradasi Performa)")
        for model_name, c in corrs.items():
            lines.append(f"### Model: `{model_name}`")
            ov = c.get("overall", {})
            m_f1 = ov.get("mmd_vs_f1_deg", {})
            w_f1 = ov.get("wasserstein_vs_f1_deg", {})

            lines.append(f"- **MMD vs F1 Degradation (Overall):**")
            lines.append(f"  - rs = `{m_f1.get('spearman_rho')}` | p-value = `{m_f1.get('p_value')}` | Signifikan: **{m_f1.get('is_significant')}**")
            if m_f1.get("warning"):
                lines.append(f"  - *Catatan:* {m_f1.get('warning')}")

            lines.append(f"- **Wasserstein vs F1 Degradation (Overall):**")
            lines.append(f"  - rs = `{w_f1.get('spearman_rho')}` | p-value = `{w_f1.get('p_value')}` | Signifikan: **{w_f1.get('is_significant')}**")
            if w_f1.get("warning"):
                lines.append(f"  - *Catatan:* {w_f1.get('warning')}")

            # Per scenario breakdown
            per_scen = c.get("per_scenario", {})
            if per_scen:
                lines.append(f"- **Dekomposisi Per-Skenario:**")
                for s_name, s_corr in per_scen.items():
                    s_m = s_corr.get("mmd_vs_f1", {}).get("spearman_rho")
                    s_w = s_corr.get("wasserstein_vs_f1", {}).get("spearman_rho")
                    lines.append(f"  - `{s_name}`: rs(MMD) = `{s_m}`, rs(Wasserstein) = `{s_w}`")

        lines.append("\n## 3. Ringkasan Kejadian Pelanggaran Batas Keandalan (Threshold Breaches)")
        total_tests = len(self.profile_df)
        breached_tests = int(self.profile_df["is_breached"].sum())
        lines.append(f"- Total Kondisi Pengujian: **{total_tests}**")
        lines.append(f"- Kondisi Melewati Ambang Batas (Gagal / Breached): **{breached_tests} ({round(breached_tests / max(total_tests, 1) * 100, 1)}%)**")
        lines.append(f"- Kondisi Aman / Dalam Toleransi: **{total_tests - breached_tests} ({round((total_tests - breached_tests) / max(total_tests, 1) * 100, 1)}%)**")

        lines.append("\n## 4. Validasi Asumsi & Keterbatasan Metodologis")
        lines.append("1. **Validitas Asumsi Covariate Shift:** Skenario A, B, dan C menggunakan gangguan sintetis terkontrol pada ruang fitur. Skenario E (Subsampling) menjamin P(Y|X) konstan secara murni melalui biased selection.")
        lines.append("2. **Dukungan Data (Support / Positivity):** Nilai `support_overlap_pct` mencatat persentase sampel shifted yang berada di dalam rentang observasi training. Skenario ekstrapolasi tinggi (outlier) ditandai sebagai uji stres batas.")
        lines.append("3. **Skala Jarak Statistik:** MMD (berbasis RBF kernel) dan Wasserstein Distance (Sliced Wasserstein) memiliki skala numerik dan sensitivitas berbeda; keduanya tidak dimaksudkan untuk diperbandingkan secara nilai absolut langsung.")

        return "\n".join(lines)
