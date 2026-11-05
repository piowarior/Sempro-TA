"""
Wasserstein Distance Module for Tabular Distributions.
Implements:
1. Univariate Wasserstein-1 (Earth Mover's Distance) per feature.
2. Multivariate Sliced Wasserstein Distance (SWD) via random unit projections.
"""

from typing import Dict, List, Tuple, Optional
import numpy as np
from scipy.stats import wasserstein_distance


def calculate_featurewise_wasserstein(
    X: np.ndarray,
    Y: np.ndarray,
    feature_names: Optional[List[str]] = None
) -> Dict[str, float]:
    """
    Computes 1D Wasserstein-1 distance for each individual feature dimension.
    """
    n_features = X.shape[1]
    if feature_names is None:
        feature_names = [f"feat_{i}" for i in range(n_features)]

    distances = {}
    for i, name in enumerate(feature_names):
        w_dist = float(wasserstein_distance(X[:, i], Y[:, i]))
        distances[name] = w_dist

    return distances


def calculate_sliced_wasserstein(
    X: np.ndarray,
    Y: np.ndarray,
    n_projections: int = 50,
    random_seed: int = 42
) -> float:
    """
    Computes Multivariate Sliced Wasserstein-1 Distance (SWD).
    Projects multivariate data onto L random 1D unit lines,
    computes 1D Wasserstein distance on each projection, and averages them.

    SW_1(X, Y) = 1/L * sum_{l=1}^L W_1(X * theta_l, Y * theta_l)
    where theta_l ~ Uniform(S^{d-1})
    """
    rng = np.random.default_rng(random_seed)
    d = X.shape[1]

    if d == 1:
        return float(wasserstein_distance(X.ravel(), Y.ravel()))

    # Generate random directions on the unit sphere S^{d-1}
    # Using standard Gaussian vectors normalized to unit length
    gaussian_projections = rng.normal(size=(d, n_projections))
    norms = np.linalg.norm(gaussian_projections, axis=0, keepdims=True)
    unit_projections = gaussian_projections / np.maximum(norms, 1e-12)

    # Project X and Y
    proj_X = X @ unit_projections  # (n, n_projections)
    proj_Y = Y @ unit_projections  # (m, n_projections)

    distances = []
    for l in range(n_projections):
        w1 = wasserstein_distance(proj_X[:, l], proj_Y[:, l])
        distances.append(w1)

    return float(np.mean(distances))


def calculate_wasserstein(
    X: np.ndarray,
    Y: np.ndarray,
    n_projections: int = 50,
    random_seed: int = 42
) -> float:
    """
    Standard interface to compute overall multivariate Wasserstein distance.
    """
    return calculate_sliced_wasserstein(X, Y, n_projections=n_projections, random_seed=random_seed)
