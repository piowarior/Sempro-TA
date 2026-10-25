"""
Unit tests for ShiftGenerator.
Verifies controlled shift injection:
- Skenario A: Location (Mean Shift)
- Skenario B: Scale (Variance Shift)
- Skenario C: Shape (Outlier Shift)
- Skenario D: Categorical Proportion Shift
- Skenario E: Pure Covariate Shift via Biased Subsampling
And validates support overlap checking.
"""

import numpy as np
import pandas as pd
import pytest
from framework.config import ShiftScenario, ShiftSeverity
from framework.shift.generator import ShiftGenerator


@pytest.fixture
def sample_data():
    rng = np.random.default_rng(42)
    df = pd.DataFrame({
        "num1": rng.normal(loc=50.0, scale=10.0, size=500),
        "num2": rng.normal(loc=100.0, scale=20.0, size=500),
        "cat1": rng.choice(["A", "B", "C"], size=500, p=[0.70, 0.20, 0.10]),
        "label": rng.choice([0, 1], size=500)
    })
    return df


def test_mean_shift(sample_data):
    gen = ShiftGenerator(random_seed=42)
    shifted_df, meta = gen.apply_shift(
        sample_data,
        scenario=ShiftScenario.MEAN_SHIFT,
        severity=ShiftSeverity.HIGH,  # 1.00
        target_features=["num1"]
    )

    orig_mean = sample_data["num1"].mean()
    shifted_mean = shifted_df["num1"].mean()
    orig_std = sample_data["num1"].std()

    assert shifted_mean > orig_mean
    assert np.isclose(shifted_mean - orig_mean, 1.00 * orig_std, atol=0.2)
    assert np.allclose(shifted_df["num2"], sample_data["num2"])
    assert "support_overlap_pct" in meta


def test_variance_shift(sample_data):
    gen = ShiftGenerator(random_seed=42)
    shifted_df, meta = gen.apply_shift(
        sample_data,
        scenario=ShiftScenario.VARIANCE_SHIFT,
        severity=ShiftSeverity.HIGH,  # 1.00
        target_features=["num1"]
    )

    orig_std = sample_data["num1"].std()
    shifted_std = shifted_df["num1"].std()

    assert shifted_std > orig_std * 2.0


def test_outlier_shift(sample_data):
    gen = ShiftGenerator(random_seed=42)
    shifted_df, meta = gen.apply_shift(
        sample_data,
        scenario=ShiftScenario.OUTLIER_SHIFT,
        severity=ShiftSeverity.HIGH,
        target_features=["num1"]
    )

    orig_max = sample_data["num1"].max()
    orig_min = sample_data["num1"].min()
    shifted_max = shifted_df["num1"].max()
    shifted_min = shifted_df["num1"].min()

    assert (shifted_max > orig_max) or (shifted_min < orig_min)
    assert meta["support_overlap_pct"] < 100.0


def test_categorical_shift(sample_data):
    gen = ShiftGenerator(random_seed=42)
    # C is the minority category (10%)
    shifted_df, meta = gen.apply_shift(
        sample_data,
        scenario=ShiftScenario.CATEGORICAL_SHIFT,
        severity=ShiftSeverity.HIGH,
        target_features=["cat1"]
    )

    orig_c_count = (sample_data["cat1"] == "C").sum()
    shifted_c_count = (shifted_df["cat1"] == "C").sum()

    # Minority category C frequency should increase significantly
    assert shifted_c_count > orig_c_count
    assert meta["shift_validity_type"] == "synthetic_categorical_perturbation"


def test_subsampling_pure_covariate_shift(sample_data):
    gen = ShiftGenerator(random_seed=42)
    shifted_df, meta = gen.apply_shift(
        sample_data,
        scenario=ShiftScenario.SUBSAMPLING_SHIFT,
        severity=ShiftSeverity.HIGH,
        target_features=["num1"]
    )

    # Subsampling maintains pure covariate shift: all pairs (X, Y) are real
    assert len(shifted_df) == len(sample_data)
    assert meta["shift_validity_type"] == "pure_covariate_shift"
    assert meta["support_overlap_pct"] == 100.0
    # Mean should have shifted towards higher values due to sigmoid weight
    assert shifted_df["num1"].mean() > sample_data["num1"].mean()


def test_shift_reproducibility(sample_data):
    gen1 = ShiftGenerator(random_seed=123)
    gen2 = ShiftGenerator(random_seed=123)

    shift1, _ = gen1.apply_shift(sample_data, ShiftScenario.OUTLIER_SHIFT, ShiftSeverity.MEDIUM)
    shift2, _ = gen2.apply_shift(sample_data, ShiftScenario.OUTLIER_SHIFT, ShiftSeverity.MEDIUM)

    pd.testing.assert_frame_equal(shift1, shift2)
