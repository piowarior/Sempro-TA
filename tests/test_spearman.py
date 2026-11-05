"""
Unit tests for Spearman Rank Correlation Analyzer.
Verifies monotonicity, p-value significance, and sample size warnings.
"""

from framework.reliability.correlation import CorrelationAnalyzer


def test_spearman_perfect_monotonic():
    # As distance increases, degradation increases strictly monotonically
    distances = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0]  # N=10
    degradations = [0.01, 0.02, 0.05, 0.08, 0.12, 0.15, 0.18, 0.22, 0.25, 0.30]

    res = CorrelationAnalyzer.compute_spearman(distances, degradations)
    assert res["spearman_rho"] == 1.0
    assert res["p_value"] < 0.05
    assert res["is_significant"] is True
    assert res["is_reliable_sample_size"] is True
    assert res["warning"] is None


def test_spearman_small_sample_warning():
    # N=5 < 10
    distances = [0.1, 0.3, 0.5, 0.7, 0.9]
    degradations = [0.02, 0.05, 0.12, 0.18, 0.25]

    res = CorrelationAnalyzer.compute_spearman(distances, degradations)
    assert res["spearman_rho"] == 1.0
    assert res["is_reliable_sample_size"] is False
    assert "Perhatian" in res["warning"]


def test_spearman_inverse_monotonic():
    distances = [0.1, 0.3, 0.5, 0.7, 0.9]
    degradations = [0.25, 0.18, 0.12, 0.05, 0.02]

    res = CorrelationAnalyzer.compute_spearman(distances, degradations)
    assert res["spearman_rho"] == -1.0


def test_spearman_too_small_sample():
    res = CorrelationAnalyzer.compute_spearman([0.1, 0.2], [0.05, 0.08])
    assert res["spearman_rho"] is None
    assert res["is_significant"] is False
