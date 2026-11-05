"""
Maximum Mean Discrepancy (MMD) with Gaussian RBF Kernel.
Implements the unbiased U-statistic estimator for MMD^2,
the median heuristic for RBF bandwidth estimation,
and subsampling (N <= 5000) for OOM mitigation on large tabular datasets.
"""

from typing import Tuple, Optional
import numpy as np
from scipy.spatial.distance import pdist, cdist


def compute_rbf_kernel_matrix(X: np.ndarray, Y: np.ndarray, gamma: float) -> np.ndarray:
    """
    Computes Gaussian RBF kernel matrix: K(X, Y) = exp(-gamma * ||x - y||^2)
    where gamma = 1 / (2 * sigma^2).
    """
    dists_sq = cdist(X, Y, metric="sqeuclidean")
    return np.exp(-gamma * dists_sq)


def estimate_median_bandwidth(X: np.ndarray, Y: np.ndarray, max_subsample: int = 2000, random_seed: int = 42) -> float:
    """
    Computes the median heuristic for Gaussian RBF kernel bandwidth:
    sigma = median(||z_i - z_j||) for pooled samples Z = X U Y.
    Returns gamma = 1.0 / (2 * sigma^2).
    """
    rng = np.random.default_rng(random_seed)
    pooled = np.vstack([X, Y])

    if len(pooled) > max_subsample:
        idx = rng.choice(len(pooled), size=max_subsample, replace=False)
        pooled_sub = pooled[idx]
    else:
        pooled_sub = pooled

    pairwise_dists = pdist(pooled_sub, metric="euclidean")
    # Filter out exact zeros to avoid division by zero
    non_zero_dists = pairwise_dists[pairwise_dists > 1e-8]

    if len(non_zero_dists) == 0:
        sigma = 1.0
    else:
        sigma = float(np.median(non_zero_dists))
        if sigma <= 1e-8:
            sigma = 1.0

    gamma = 1.0 / (2.0 * (sigma ** 2))
    return gamma


def calculate_mmd(
    X: np.ndarray,
    Y: np.ndarray,
    gamma: Optional[float] = None,
    subsample_limit: int = 5000,
    random_seed: int = 42
) -> Tuple[float, float, float]:
    """
    Calculates the unbiased U-statistic MMD^2 and MMD between samples X and Y.

    Formula:
    MMD^2(X, Y) = 1/[n(n-1)] * sum_{i!=j} k(x_i, x_j)
                + 1/[m(m-1)] * sum_{i!=j} k(y_i, y_j)
                - 2/[n*m]    * sum_{i,j} k(x_i, y_j)

    Returns:
        (mmd_distance, mmd_squared, gamma_used)
    """
    rng = np.random.default_rng(random_seed)

    # Subsampling OOM mitigation
    n = len(X)
    m = len(Y)

    if n > subsample_limit:
        idx_x = rng.choice(n, size=subsample_limit, replace=False)
        X = X[idx_x]
        n = subsample_limit

    if m > subsample_limit:
        idx_y = rng.choice(m, size=subsample_limit, replace=False)
        Y = Y[idx_y]
        m = subsample_limit

    if n < 2 or m < 2:
        raise ValueError("At least 2 samples required in both X and Y to compute unbiased MMD.")

    if gamma is None:
        gamma = estimate_median_bandwidth(X, Y, max_subsample=min(2000, n + m), random_seed=random_seed)

    K_xx = compute_rbf_kernel_matrix(X, X, gamma)
    K_yy = compute_rbf_kernel_matrix(Y, Y, gamma)
    K_xy = compute_rbf_kernel_matrix(X, Y, gamma)

    # Unbiased U-statistic estimators: exclude diagonals
    term_xx = (np.sum(K_xx) - np.trace(K_xx)) / (n * (n - 1))
    term_yy = (np.sum(K_yy) - np.trace(K_yy)) / (m * (m - 1))
    term_xy = np.sum(K_xy) / (n * m)

    mmd_squared = float(term_xx + term_yy - 2.0 * term_xy)
    mmd_dist = float(np.sqrt(max(0.0, mmd_squared)))

    return mmd_dist, mmd_squared, gamma
