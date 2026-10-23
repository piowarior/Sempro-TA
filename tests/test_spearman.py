"""
Unit tests for Spearman Rank Correlation Analyzer.
"""

from framework.reliability.correlation import CorrelationAnalyzer


def test_spearman_perfect_monotonic():
    # As distance increases, degradation increases strictly monotonically
    distances = [0.1, 0.3, 0.5, 0.7, 0.9]
    degradations = [0.02, 0.05, 0.12, 0.18, 0.25]

    res = CorrelationAnalyzer.compute_spearman(distances, degradations)
    assert res["spearman_rho"] == 1.0
    assert res["p_value"] < 0.05


def test_spearman_inverse_monotonic():
    distances = [0.1, 0.3, 0.5, 0.7, 0.9]
    degradations = [0.25, 0.18, 0.12, 0.05, 0.02]

    res = CorrelationAnalyzer.compute_spearman(distances, degradations)
    assert res["spearman_rho"] == -1.0


def test_spearman_small_sample():
    res = CorrelationAnalyzer.compute_spearman([0.1, 0.2], [0.05, 0.08])
    assert res["spearman_rho"] is None
