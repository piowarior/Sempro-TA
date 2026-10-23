"""
Performance Degradation Calculator.
Calculates absolute degradation and relative degradation percentage
relative to baseline test performance.
"""

from typing import Dict, Tuple, Optional
from ..config import EvaluationMetrics


class DegradationCalculator:
    """
    Computes performance degradation metrics.
    """

    @staticmethod
    def calculate_degradations(
        baseline: EvaluationMetrics,
        shifted: EvaluationMetrics
    ) -> Tuple[Dict[str, float], Dict[str, Optional[float]]]:
        """
        Returns:
            (absolute_degradation, relative_degradation_pct)
        """
        metrics = ["accuracy", "precision", "recall", "f1_score", "roc_auc"]
        abs_deg = {}
        rel_deg = {}

        for m in metrics:
            s_base = getattr(baseline, m)
            s_shift = getattr(shifted, m)

            # Absolute: S_baseline - S_shifted
            # (positive = performance dropped, negative = performance improved)
            diff_abs = s_base - s_shift
            abs_deg[m] = round(diff_abs, 4)

            # Relative percentage: (diff_abs / s_base) * 100%
            if abs(s_base) > 1e-6:
                diff_rel = (diff_abs / s_base) * 100.0
                rel_deg[m] = round(diff_rel, 2)
            else:
                rel_deg[m] = None  # Undefined when baseline is zero

        return abs_deg, rel_deg
