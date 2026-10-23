"""
Statistical Analysis of Distance vs. Performance Degradation.
Implements Spearman Rank Correlation (r_s) to evaluate whether distribution distance
statistically corresponds to model performance degradation.
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
        degradations: List[float]
    ) -> Dict[str, Optional[float]]:
        """
        Computes Spearman r_s and p-value.
        Handles sample sizes < 3 safely.
        """
        if len(distances) < 3 or len(degradations) < 3:
            return {"spearman_rho": None, "p_value": None, "n_samples": len(distances)}

        # Check if arrays are constant
        if np.all(distances == distances[0]) or np.all(degradations == degradations[0]):
            return {"spearman_rho": 0.0, "p_value": 1.0, "n_samples": len(distances)}

        res = spearmanr(distances, degradations)
        rho = float(res.statistic) if hasattr(res, "statistic") else float(res.correlation)
        p_val = float(res.pvalue)

        return {
            "spearman_rho": round(rho, 4) if not np.isnan(rho) else 0.0,
            "p_value": round(p_val, 4) if not np.isnan(p_val) else 1.0,
            "n_samples": len(distances)
        }
