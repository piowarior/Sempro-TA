"""
Controlled Covariate Shift Generator.
Implements:
- Skenario A: Feature Location Shift (Mean Shift)
- Skenario B: Feature Scale/Variance Shift (Variance Shift)
- Skenario C: Feature Shape / Outlier Ingestion (Extreme Shift)

All shifts are parameterized by ShiftSeverity (Rendah: 0.25, Sedang: 0.50, Tinggi: 1.00)
and are strictly reproducible with fixed random seed.
"""

from typing import List, Optional, Tuple, Dict, Any
import numpy as np
import pandas as pd
from ..config import ShiftScenario, ShiftSeverity


class ShiftGenerator:
    """
    Applies controlled covariate shifts to evaluation/test data.
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
        numeric_cols = [
            c for c in shifted_df.columns if pd.api.types.is_numeric_dtype(shifted_df[c])
        ]

        # If target features not specified, apply to all numeric features
        if not target_features:
            target_features = numeric_cols
        else:
            # Filter only numeric target features
            target_features = [f for f in target_features if f in numeric_cols]

        if not target_features:
            raise ValueError("No valid numeric features found to apply covariate shift.")

        # Reference statistics from train (or test if train not provided)
        ref_df = train_reference_df if train_reference_df is not None else df_test

        metadata: Dict[str, Any] = {
            "scenario": scenario.value,
            "severity": float(severity.value),
            "target_features": target_features,
            "random_seed": self.random_seed,
            "feature_stats_before": {},
            "feature_stats_after": {},
        }

        sev_val = float(severity.value)

        for col in target_features:
            ref_mean = float(ref_df[col].mean())
            ref_std = float(ref_df[col].std()) if float(ref_df[col].std()) > 1e-9 else 1.0
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
                # Scale around reference mean: (x - mean) * (1.0 + severity * 1.5)
                scale_factor = 1.0 + (sev_val * 1.5)
                shifted_series = ref_mean + (orig_series - ref_mean) * scale_factor

            elif scenario == ShiftScenario.OUTLIER_SHIFT:
                # Skenario C: Feature Shape / Outlier Ingestion
                # Inject extreme values into a fraction of samples proportional to severity
                outlier_fraction = min(0.40, sev_val * 0.35)
                n_samples = len(orig_series)
                n_outliers = max(1, int(n_samples * outlier_fraction))
                outlier_indices = self.rng.choice(n_samples, size=n_outliers, replace=False)

                shifted_series = orig_series.copy()
                # Outlier magnitude: 3.5 to 6.0 std deviations away
                extreme_multiplier = 3.5 + (sev_val * 2.5)
                noise_direction = self.rng.choice([-1, 1], size=n_outliers)
                outliers = ref_mean + noise_direction * extreme_multiplier * ref_std
                shifted_series[outlier_indices] = outliers

            else:
                raise NotImplementedError(f"Shift scenario '{scenario}' is not supported.")

            shifted_df[col] = shifted_series

            metadata["feature_stats_after"][col] = {
                "mean": float(np.mean(shifted_series)),
                "std": float(np.std(shifted_series)),
                "min": float(np.min(shifted_series)),
                "max": float(np.max(shifted_series)),
            }

        return shifted_df, metadata
