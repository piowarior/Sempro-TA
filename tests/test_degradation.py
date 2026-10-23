"""
Unit tests for Performance Degradation and Dynamic Reliability Threshold.
Verifies:
1. Absolute degradation calculation: S_baseline - S_shifted.
2. Negative degradation preservation (when shifted performs better).
3. Relative degradation percentage calculation.
4. Dynamic Threshold Check: Degradation > 1.5 * Std_Dev(CV).
"""

import pytest
from framework.config import EvaluationMetrics, ThresholdStatus
from framework.reliability.degradation import DegradationCalculator
from framework.reliability.threshold import ReliabilityThresholdChecker


def test_degradation_calculation_drop():
    baseline = EvaluationMetrics(
        accuracy=0.85,
        precision=0.82,
        recall=0.80,
        f1_score=0.81,
        roc_auc=0.88
    )
    shifted = EvaluationMetrics(
        accuracy=0.70,
        precision=0.65,
        recall=0.60,
        f1_score=0.62,
        roc_auc=0.75
    )

    abs_deg, rel_deg = DegradationCalculator.calculate_degradations(baseline, shifted)

    # Absolute degradation: 0.81 - 0.62 = 0.19
    assert abs_deg["f1_score"] == 0.19
    # Relative degradation %: (0.19 / 0.81) * 100 = 23.46%
    assert pytest.approx(rel_deg["f1_score"], 0.1) == 23.46


def test_degradation_calculation_negative_preserved():
    # Model performs slightly better on shifted test set
    baseline = EvaluationMetrics(accuracy=0.80, precision=0.80, recall=0.80, f1_score=0.80, roc_auc=0.85)
    shifted = EvaluationMetrics(accuracy=0.83, precision=0.83, recall=0.83, f1_score=0.83, roc_auc=0.88)

    abs_deg, rel_deg = DegradationCalculator.calculate_degradations(baseline, shifted)

    # Must preserve negative degradation (-0.03) rather than zeroing out
    assert abs_deg["f1_score"] == -0.03
    assert rel_deg["f1_score"] == -3.75


def test_dynamic_threshold_status():
    cv_std = 0.04  # 5-fold CV std dev
    multiplier = 1.5
    # Threshold boundary = 1.5 * 0.04 = 0.06

    # Case 1: Small drop (0.03 < 0.06) -> SAFE
    status_safe, thresh_val = ReliabilityThresholdChecker.evaluate_dynamic_threshold(
        absolute_degradation=0.03,
        cv_std_dev=cv_std,
        multiplier=multiplier
    )
    assert status_safe == ThresholdStatus.SAFE
    assert thresh_val == 0.06

    # Case 2: Excessive drop (0.12 > 0.06) -> BREACHED
    status_breached, thresh_val = ReliabilityThresholdChecker.evaluate_dynamic_threshold(
        absolute_degradation=0.12,
        cv_std_dev=cv_std,
        multiplier=multiplier
    )
    assert status_breached == ThresholdStatus.BREACHED
    assert thresh_val == 0.06
