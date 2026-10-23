"""
Reliability Profile Generator and Report Builder.
Assembles the complete matrix of experimental conditions, distances,
performance metrics, degradations, threshold statuses, and Spearman correlations.
Supports export to CSV, JSON, and Markdown summaries.
"""

from typing import List, Dict, Any, Optional
import json
import pandas as pd
from ..config import ShiftResult, BaselineEvaluation, ThresholdStatus
from .correlation import CorrelationAnalyzer


class ReliabilityProfile:
    """
    Constructs, queries, and exports the final ML Reliability Profile.
    """

    def __init__(
        self,
        experiment_id: str,
        dataset_name: str,
        baselines: Dict[str, BaselineEvaluation],
        shift_results: List[ShiftResult]
    ):
        self.experiment_id = experiment_id
        self.dataset_name = dataset_name
        self.baselines = baselines
        self.shift_results = shift_results
        self.profile_df = self._build_dataframe()

    def _build_dataframe(self) -> pd.DataFrame:
        rows = []
        for res in self.shift_results:
            scenario_name = res.scenario.value
            severity_val = float(res.severity.value)
            mmd = res.mmd_distance
            wass = res.wasserstein_distance

            for model_name, shifted_m in res.shifted_metrics.items():
                base_eval = self.baselines[model_name]
                base_m = base_eval.test_metrics
                cv_f1_std = base_eval.cv_scores_std.get("f1_score", 0.0)

                abs_deg = res.degradation_absolute[model_name]
                rel_deg = res.degradation_relative_pct[model_name]
                status = res.threshold_status[model_name].value
                thresh_val = res.threshold_value[model_name]

                row = {
                    "experiment_id": self.experiment_id,
                    "dataset": self.dataset_name,
                    "model": model_name,
                    "shift_scenario": scenario_name,
                    "severity": severity_val,
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
                    "threshold_value_1_5x": thresh_val,
                    "threshold_status": status,
                    "is_breached": 1 if status == ThresholdStatus.BREACHED.value else 0
                }
                rows.append(row)

        return pd.DataFrame(rows)

    def compute_correlations(self) -> Dict[str, Dict[str, Any]]:
        """
        Computes Spearman rank correlation (distance vs. degradation) per model.
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

            results[model_name] = {
                "mmd_vs_f1_deg": CorrelationAnalyzer.compute_spearman(mmd_vals, deg_f1),
                "wasserstein_vs_f1_deg": CorrelationAnalyzer.compute_spearman(wass_vals, deg_f1),
                "mmd_vs_auc_deg": CorrelationAnalyzer.compute_spearman(mmd_vals, deg_auc),
                "wasserstein_vs_auc_deg": CorrelationAnalyzer.compute_spearman(wass_vals, deg_auc),
            }

        return results

    def to_csv(self, filepath: str) -> None:
        self.profile_df.to_csv(filepath, index=False)

    def to_json(self, filepath: str) -> None:
        data = {
            "experiment_id": self.experiment_id,
            "dataset": self.dataset_name,
            "correlations": self.compute_correlations(),
            "records": self.profile_df.to_dict(orient="records")
        }
        with open(filepath, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=2)

    def generate_markdown_summary(self) -> str:
        """
        Creates an executive summary in Markdown format.
        """
        corrs = self.compute_correlations()
        lines = [
            f"# Ringkasan Reliability Profile: {self.dataset_name}",
            f"**Experiment ID:** `{self.experiment_id}`\n",
            "## 1. Performa Baseline & Ambang Batas Toleransi (5-Fold CV)",
        ]

        for model_name, base in self.baselines.items():
            lines.append(f"- **{model_name}:**")
            lines.append(f"  - Test F1-Score: {base.test_metrics.f1_score} | ROC-AUC: {base.test_metrics.roc_auc} | Accuracy: {base.test_metrics.accuracy}")
            lines.append(f"  - CV F1 Mean: {base.cv_scores_mean.get('f1_score')} ± {base.cv_scores_std.get('f1_score')}")
            lines.append(f"  - Batas Toleransi Dinamis (1.5x Std Dev CV): `{round(1.5 * max(base.cv_scores_std.get('f1_score', 0.01), 0.01), 4)}`")

        lines.append("\n## 2. Analisis Korelasi Spearman Rank (Distance vs F1 Degradation)")
        for model_name, c in corrs.items():
            lines.append(f"- **{model_name}:**")
            m_f1 = c["mmd_vs_f1_deg"]
            w_f1 = c["wasserstein_vs_f1_deg"]
            lines.append(f"  - MMD vs F1 Degradation: rs = {m_f1.get('spearman_rho')} (p = {m_f1.get('p_value')})")
            lines.append(f"  - Wasserstein vs F1 Degradation: rs = {w_f1.get('spearman_rho')} (p = {w_f1.get('p_value')})")

        lines.append("\n## 3. Ringkasan Kejadian Pelanggaran Ambang Batas (Threshold Breaches)")
        total_tests = len(self.profile_df)
        breached_tests = int(self.profile_df["is_breached"].sum())
        lines.append(f"- Total Kondisi Pengujian: {total_tests}")
        lines.append(f"- Kondisi Melewati Ambang Batas (Breached): {breached_tests} ({round(breached_tests / max(total_tests, 1) * 100, 1)}%)")

        return "\n".join(lines)
