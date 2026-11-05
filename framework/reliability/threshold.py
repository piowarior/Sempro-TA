"""
Dynamic Reliability Threshold Evaluator.
Evaluates whether model degradation exceeds the empirical tolerance boundary:
Degradation > 1.5 * Std_Dev(CV).
"""

from typing import Dict, Tuple
from ..config import ThresholdStatus, ThresholdPolicy


class ReliabilityThresholdChecker:
    """
    Checks if model degradation breaches the dynamic cross-validation standard deviation threshold.
    """

    @staticmethod
    def evaluate_dynamic_threshold(
        absolute_degradation: float,
        cv_std_dev: float,
        multiplier: float = 1.5
    ) -> Tuple[ThresholdStatus, float]:
        """
        Calculates dynamic threshold value and returns threshold status.
        Threshold boundary: T = multiplier * cv_std_dev
        If degradation > T: BREACHED (Melewati Ambang Batas)
        Else: SAFE (Aman / Dalam Batas Toleransi)
        """
        # Ensure a minimum sensible threshold if cv_std_dev is tiny
        effective_std = max(cv_std_dev, 0.01)
        threshold_value = round(multiplier * effective_std, 4)

        if absolute_degradation > threshold_value:
            status = ThresholdStatus.BREACHED
        else:
            status = ThresholdStatus.SAFE

        return status, threshold_value
