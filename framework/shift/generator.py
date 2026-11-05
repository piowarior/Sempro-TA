"""
Controlled Covariate Shift Generator with Support & Positivity Validation.
Implements:
- Skenario A: Feature Location Shift (Mean Shift)
- Skenario B: Feature Scale/Variance Shift (Variance Shift)
- Skenario C: Feature Shape / Outlier Ingestion (Extreme Shift)
- Skenario D: Categorical Proportion Shift (Frequency Permutation)
- Skenario E: Pure Covariate Shift via Biased Selection (Guaranteed P(Y|X) constant)

Includes empirical support/positivity overlap checking to assess whether shifted
data extrapolates beyond the training feature support boundary.
"""

from typing import List, Optional, Tuple, Dict, Any
import numpy as np
import pandas as pd
from ..config import ShiftScenario, ShiftSeverity


class ShiftGenerator:
    """
    Applies controlled covariate shifts to evaluation/test data with support validation.
    """

    def __init__(self, random_seed: int = 42):
        self.random_seed = random_seed
        self.rng = np.random.default_rng(random_seed)

    def apply_shift(
        self,
        df_test: pd.DataFrame,
        scenario: ShiftScenario,
        severity: ShiftSeverity,
        target_features: Optional[List[str]] = None,
        train_reference_df: Optional[pd.DataFrame] = None,
    ) -> Tuple[pd.DataFrame, Dict[str, Any]]:
        """
        Applies a specific covariate shift scenario with given severity.
        Returns:
            (shifted_df, shift_metadata)
        """
        shifted_df = df_test.copy()
        ref_df = train_reference_df if train_reference_df is not None else df_test

        numeric_cols = [
            c for c in shifted_df.columns if pd.api.types.is_numeric_dtype(shifted_df[c])
        ]
        categorical_cols = [
            c for c in shifted_df.columns if c not in numeric_cols
        ]

        sev_val = float(severity.value)

        metadata: Dict[str, Any] = {
            "scenario": scenario.value,
            "severity": sev_val,
            "random_seed": self.random_seed,
            "target_features": [],
            "support_overlap_pct": 100.0,
            "shift_validity_type": "synthetic_perturbation",
            "assumption_notes": "",
            "feature_stats_before": {},
            "feature_stats_after": {},
        }

        # -------------------------------------------------------------
        # Skenario E: Pure Covariate Shift via Biased Rejection Sampling
        # -------------------------------------------------------------
        if scenario == ShiftScenario.SUBSAMPLING_SHIFT:
            metadata["shift_validity_type"] = "pure_covariate_shift"
            metadata["assumption_notes"] = (
                "P(Y|X) dipertahankan konstan secara murni karena observasi (X, Y) diambil "
                "dari pasangan asli melalui sampling probabilitas terbobot (biased selection)."
            )

            # Pick a primary numeric feature to bias sampling
            bias_col = target_features[0] if (target_features and target_features[0] in numeric_cols) else (
                numeric_cols[0] if numeric_cols else None
            )

            if bias_col is None:
                raise ValueError("Subsampling shift requires at least one numeric feature for biased selection.")

            metadata["target_features"] = [bias_col]
            x_vals = shifted_df[bias_col].to_numpy().astype(float)
            mu = float(ref_df[bias_col].mean())
            sigma = float(ref_df[bias_col].std()) if float(ref_df[bias_col].std()) > 1e-9 else 1.0
            z = (x_vals - mu) / sigma

            # Sigmoid selection weights: higher values of x get exponentially higher chance
            beta = sev_val * 2.5
            weights = 1.0 / (1.0 + np.exp(-beta * z))
            probs = weights / np.sum(weights)

            # Sample with replacement to preserve sample size N
            n_samples = len(shifted_df)
            selected_idx = self.rng.choice(n_samples, size=n_samples, p=probs, replace=True)
            sampled_df = shifted_df.iloc[selected_idx].reset_index(drop=True)

            metadata["support_overlap_pct"] = 100.0  # By definition within original support
            metadata["feature_stats_before"][bias_col] = {
                "mean": float(np.mean(x_vals)),
                "std": float(np.std(x_vals)),
            }
            sampled_vals = sampled_df[bias_col].to_numpy().astype(float)
            metadata["feature_stats_after"][bias_col] = {
                "mean": float(np.mean(sampled_vals)),
                "std": float(np.std(sampled_vals)),
            }
            return sampled_df, metadata

        # -------------------------------------------------------------
        # Skenario D: Categorical Proportion Shift
        # -------------------------------------------------------------
        elif scenario == ShiftScenario.CATEGORICAL_SHIFT:
            target_cats = target_features if target_features else categorical_cols
            target_cats = [c for c in target_cats if c in categorical_cols]

            if not target_cats:
                # If no categorical columns exist in dataset, raise clean error
                raise ValueError("Dataset does not contain categorical columns for Categorical Shift.")

            metadata["target_features"] = target_cats
            metadata["shift_validity_type"] = "synthetic_categorical_perturbation"
            metadata["assumption_notes"] = (
                "Proporsi kategori diubah secara terkontrol dengan merelokasi sebagian observasi "
                "ke kategori berfrekuensi rendah sesuai severity."
            )

            for col in target_cats:
                categories, counts = np.unique(ref_df[col].astype(str), return_counts=True)
                # Sort categories by frequency ascending (minority first)
                sorted_cats = categories[np.argsort(counts)]
                cat_series = shifted_df[col].astype(str).to_numpy()

                # Perturbation fraction proportional to severity
                perturb_frac = min(0.70, sev_val * 0.60)
                n_samples = len(cat_series)
                n_perturb = int(n_samples * perturb_frac)
                perturb_indices = self.rng.choice(n_samples, size=n_perturb, replace=False)

                # Reassign to minority category
                minority_cat = sorted_cats[0]
                cat_series[perturb_indices] = minority_cat
                shifted_df[col] = cat_series

            metadata["support_overlap_pct"] = 100.0
            return shifted_df, metadata

        # -------------------------------------------------------------
        # Skenario A, B, C: Numeric Perturbations (Mean, Variance, Outlier)
        # -------------------------------------------------------------
        target_nums = target_features if target_features else numeric_cols
        target_nums = [c for c in target_nums if c in numeric_cols]

        if not target_nums:
            raise ValueError("No valid numeric features found to apply covariate shift.")

        metadata["target_features"] = target_nums
        metadata["shift_validity_type"] = "synthetic_perturbation"
        metadata["assumption_notes"] = (
            "Transformasi fitur numerik sintetis. Asumsi P(Y|X) tetap harus dipandang sebagai "
            "kondisi operasional terkontrol dengan potensi ekstrapolasi support."
        )

        out_of_support_counts = 0
        total_eval_points = len(shifted_df) * len(target_nums)

        for col in target_nums:
            ref_mean = float(ref_df[col].mean())
            ref_std = float(ref_df[col].std()) if float(ref_df[col].std()) > 1e-9 else 1.0
            ref_min = float(ref_df[col].min())
            ref_max = float(ref_df[col].max())

            orig_series = shifted_df[col].to_numpy().astype(float)

            metadata["feature_stats_before"][col] = {
                "mean": float(np.mean(orig_series)),
                "std": float(np.std(orig_series)),
                "min": float(np.min(orig_series)),
                "max": float(np.max(orig_series)),
            }

            if scenario == ShiftScenario.MEAN_SHIFT:
                # Skenario A: Mean Shift (Delta = severity * std_train)
                delta = sev_val * ref_std
                shifted_series = orig_series + delta

            elif scenario == ShiftScenario.VARIANCE_SHIFT:
                # Skenario B: Variance Shift
                scale_factor = 1.0 + (sev_val * 1.5)
                shifted_series = ref_mean + (orig_series - ref_mean) * scale_factor

            elif scenario == ShiftScenario.OUTLIER_SHIFT:
                # Skenario C: Feature Shape / Outlier Ingestion
                outlier_fraction = min(0.40, sev_val * 0.35)
                n_samples = len(orig_series)
                n_outliers = max(1, int(n_samples * outlier_fraction))
                outlier_indices = self.rng.choice(n_samples, size=n_outliers, replace=False)

                shifted_series = orig_series.copy()
                extreme_multiplier = 3.5 + (sev_val * 2.5)
                noise_direction = self.rng.choice([-1, 1], size=n_outliers)
                outliers = ref_mean + noise_direction * extreme_multiplier * ref_std
                shifted_series[outlier_indices] = outliers

            else:
                raise NotImplementedError(f"Shift scenario '{scenario}' is not supported.")

            shifted_df[col] = shifted_series

            # Support / Positivity overlap check: samples outside [ref_min, ref_max]
            outside_mask = (shifted_series < ref_min) | (shifted_series > ref_max)
            out_of_support_counts += int(np.sum(outside_mask))

            metadata["feature_stats_after"][col] = {
                "mean": float(np.mean(shifted_series)),
                "std": float(np.std(shifted_series)),
                "min": float(np.min(shifted_series)),
                "max": float(np.max(shifted_series)),
                "pct_outside_train_support": round(float(np.mean(outside_mask)) * 100.0, 2),
            }

        overlap_pct = 100.0 - (out_of_support_counts / max(total_eval_points, 1) * 100.0)
        metadata["support_overlap_pct"] = round(max(0.0, overlap_pct), 2)

        return shifted_df, metadata
