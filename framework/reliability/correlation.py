"""
Statistical Analysis of Distance vs. Performance Degradation.
Implements Spearman Rank Correlation (r_s), p-value significance tests,
sample size reliability checks, and per-scenario decomposition.
"""

from typing import Dict, List, Any, Optional
import numpy as np
from scipy.stats import spearmanr


class CorrelationAnalyzer:
    """
    Computes Spearman's rank correlation coefficient (r_s) and p-values
    between distribution distances (MMD, Wasserstein) and degradations.
    """

    @staticmethod
    def compute_spearman(
        distances: List[float],
        degradations: List[float],
        alpha: float = 0.05
    ) -> Dict[str, Any]:
        """
        Computes Spearman r_s, p-value, significance, and sample adequacy check.
        """
        n = len(distances)
        if n < 3 or len(degradations) < 3:
            return {
                "spearman_rho": None,
                "p_value": None,
                "n_samples": n,
                "is_significant": False,
                "is_reliable_sample_size": False,
                "warning": f"Jumlah sampel terlalu kecil (N={n} < 3) untuk menghitung korelasi."
            }

        # Check if arrays are constant
        if np.all(distances == distances[0]) or np.all(degradations == degradations[0]):
            return {
                "spearman_rho": 0.0,
                "p_value": 1.0,
                "n_samples": n,
                "is_significant": False,
                "is_reliable_sample_size": n >= 10,
                "warning": "Data konstan terdeteksi (varians nol)."
            }

        res = spearmanr(distances, degradations)
        rho = float(res.statistic) if hasattr(res, "statistic") else float(res.correlation)
        p_val = float(res.pvalue)

        is_sig = bool(p_val < alpha) if not np.isnan(p_val) else False
        is_reliable_n = bool(n >= 10)

        warning_msg = None
        if not is_reliable_n:
            warning_msg = (
                f"Perhatian: Jumlah observasi N={n} < 10. Nilai korelasi rentan terhadap "
                "korelasi palsu (spurious correlation). Disarankan menambah variasi kondisi."
            )

        return {
            "spearman_rho": round(rho, 4) if not np.isnan(rho) else 0.0,
            "p_value": round(p_val, 4) if not np.isnan(p_val) else 1.0,
            "n_samples": n,
            "is_significant": is_sig,
            "is_reliable_sample_size": is_reliable_n,
            "warning": warning_msg
        }
