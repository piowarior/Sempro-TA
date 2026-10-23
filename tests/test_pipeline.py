"""
Integration tests for StressTestingPipeline.
Validates end-to-end execution, artifact generation, and reproducibility.
"""

import os
import tempfile
import pytest
from framework.config import ExperimentConfig, ModelAlgorithm, ShiftScenario, ShiftSeverity
from framework.pipeline import StressTestingPipeline
from framework.dataset.loader import generate_credit_default_dataset


def test_pipeline_end_to_end():
    config = ExperimentConfig(
        dataset_name="Test_Credit_Dataset",
        target_column="default",
        test_size=0.25,
        random_seed=42,
        algorithms=[ModelAlgorithm.LOGISTIC_REGRESSION, ModelAlgorithm.XGBOOST],
        scenarios=[ShiftScenario.MEAN_SHIFT, ShiftScenario.OUTLIER_SHIFT],
        severities=[ShiftSeverity.LOW, ShiftSeverity.HIGH],
        cv_folds=3,
        threshold_std_multiplier=1.5
    )

    df = generate_credit_default_dataset(n_samples=400, random_seed=42)
    pipeline = StressTestingPipeline(config=config)
    profile = pipeline.run(df=df)

    assert profile is not None
    assert len(profile.baselines) == 2
    assert "logistic_regression" in profile.baselines
    assert "xgboost" in profile.baselines

    # Check 5-fold CV stats
    lr_base = profile.baselines["logistic_regression"]
    assert "f1_score" in lr_base.cv_scores_mean
    assert "f1_score" in lr_base.cv_scores_std

    # Check DataFrame profile rows: 2 scenarios * 2 severities * 2 models = 8 rows
    df_res = profile.profile_df
    assert len(df_res) == 8
    assert "mmd_distance" in df_res.columns
    assert "wasserstein_distance" in df_res.columns
    assert "threshold_status" in df_res.columns
    assert "threshold_value_1_5x" in df_res.columns

    # Check exports
    with tempfile.TemporaryDirectory() as tmpdir:
        csv_path = os.path.join(tmpdir, "test_profile.csv")
        json_path = os.path.join(tmpdir, "test_profile.json")

        profile.to_csv(csv_path)
        profile.to_json(json_path)

        assert os.path.exists(csv_path)
        assert os.path.exists(json_path)
        assert os.path.getsize(csv_path) > 100
        assert os.path.getsize(json_path) > 100

    # Summary report
    summary_md = profile.generate_markdown_summary()
    assert "# Ringkasan Reliability Profile" in summary_md
