"""
Unit tests for Distribution Distance measures (MMD and Wasserstein).
"""

import numpy as np
import pytest
from framework.distance.mmd import calculate_mmd
from framework.distance.wasserstein import calculate_wasserstein, calculate_featurewise_wasserstein


def test_mmd_identical_distributions():
    rng = np.random.default_rng(42)
    # Identical standard Gaussian distribution
    X = rng.normal(loc=0.0, scale=1.0, size=(400, 4))
    Y = rng.normal(loc=0.0, scale=1.0, size=(400, 4))

    mmd_dist, mmd_sq, _ = calculate_mmd(X, Y, random_seed=42)
    # MMD for two identical distributions should be very close to 0
    assert mmd_dist < 0.15


def test_mmd_diverging_distributions():
    rng = np.random.default_rng(42)
    X = rng.normal(loc=0.0, scale=1.0, size=(400, 3))
    # Shifted distribution (loc = 3.0)
    Y = rng.normal(loc=3.0, scale=1.0, size=(400, 3))

    mmd_dist, mmd_sq, _ = calculate_mmd(X, Y, random_seed=42)
    # Significant divergence
    assert mmd_dist > 0.4
    assert mmd_sq > 0.15


def test_wasserstein_distance():
    rng = np.random.default_rng(42)
    X = rng.normal(loc=0.0, scale=1.0, size=(300, 3))
    # Shifted distribution by +2.0 in all dimensions
    Y = X + 2.0

    w_dist = calculate_wasserstein(X, Y, n_projections=40, random_seed=42)
    # Wasserstein distance should reflect the location translation (~2.0)
    assert w_dist > 1.0


def test_mmd_subsampling():
    rng = np.random.default_rng(42)
    # Large dataset N=6000 (exceeds limit 500)
    X = rng.normal(size=(600, 2))
    Y = rng.normal(loc=1.0, size=(600, 2))

    mmd_dist, _, _ = calculate_mmd(X, Y, subsample_limit=200, random_seed=42)
    assert mmd_dist > 0.0
