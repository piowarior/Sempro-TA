"""
End-to-End Stress Testing Pipeline Orchestrator.
Executes the full experimental flow:
1. Data Input & Validation
2. Leakage-free Stratified Preprocessing
3. Baseline Model Training & 5-Fold Cross Validation
4. Controlled Shift Injection (Location, Scale, Outlier)
5. Distribution Distance Calculation (MMD & Wasserstein with OOM mitigation)
6. Shifted Evaluation & Degradation Assessment
7. Dynamic Threshold Checking (1.5x CV Std Dev)
8. Reliability Profile Generation & Spearman Rank Correlation
"""

import os
import uuid
from typing import Optional, Dict, Any, List
import pandas as pd
import numpy as np

from .config import (
    ExperimentConfig,
    ShiftScenario,
    ShiftSeverity,
    ModelAlgorithm,
    ShiftResult,
    BaselineEvaluation,
    ThresholdStatus
)
from .dataset.validator import DatasetValidator
from .dataset.loader import split_train_test, generate_credit_default_dataset
from .dataset.preprocessor import TabularPreprocessor
from .shift.generator import ShiftGenerator
from .distance.mmd import calculate_mmd
from .distance.wasserstein import calculate_wasserstein
from .models.baseline import BaselineTrainer
from .models.evaluator import ModelEvaluator
from .reliability.degradation import DegradationCalculator
from .reliability.threshold import ReliabilityThresholdChecker
from .reliability.profile import ReliabilityProfile


class StressTestingPipeline:
    """
    Orchestrates the quantitative experimental stress testing workflow.
    """

    def __init__(self, config: Optional[ExperimentConfig] = None):
        self.config = config or ExperimentConfig(
            dataset_name="Credit_Card_Default_Benchmark",
            target_column="default",
            random_seed=42
        )
        self.experiment_id = f"exp_{uuid.uuid4().hex[:8]}"
        self.preprocessor = TabularPreprocessor(scale_numeric=True)
        self.shift_generator = ShiftGenerator(random_seed=self.config.random_seed)
        self.baseline_trainer = BaselineTrainer(
            random_seed=self.config.random_seed,
            cv_folds=self.config.cv_folds
        )

        self.baselines: Dict[str, BaselineEvaluation] = {}
        self.trained_models: Dict[str, Any] = {}
        self.shift_results: List[ShiftResult] = []
        self.reliability_profile: Optional[ReliabilityProfile] = None

    def run(self, df: Optional[pd.DataFrame] = None) -> ReliabilityProfile:
        """
        Executes the entire end-to-end stress testing pipeline.
        """
        # Step 1: Input & Validate Data
        if df is None:
            df = generate_credit_default_dataset(n_samples=2500, random_seed=self.config.random_seed)

        inspection = DatasetValidator.inspect(df, self.config.target_column)

        # Step 2: Leakage-free Stratified Split
        train_df, test_df = split_train_test(
            df,
            target_column=self.config.target_column,
            test_size=self.config.test_size,
            random_seed=self.config.random_seed
        )

        feature_cols = [c for c in df.columns if c != self.config.target_column]
        y_train = train_df[self.config.target_column].to_numpy()
        y_test = test_df[self.config.target_column].to_numpy()

        # Fit preprocessor STRICTLY on train_df
        X_train_df = train_df[feature_cols]
        X_test_df = test_df[feature_cols]

        X_train_proc = self.preprocessor.fit_transform(X_train_df)
        X_test_proc = self.preprocessor.transform(X_test_df)

        # Step 3: Train Baseline Models with 5-Fold Stratified CV
        for algo in self.config.algorithms:
            model, base_eval = self.baseline_trainer.train_and_evaluate(
                algo,
                X_train_proc,
                y_train,
                X_test_proc,
                y_test
            )
            self.trained_models[algo.value] = model
            self.baselines[algo.value] = base_eval

        # Step 4: Iterate Controlled Covariate Shift Scenarios & Severities
        self.shift_results = []
        numeric_targets = inspection["numeric_features"]

        for scenario in self.config.scenarios:
            for severity in self.config.severities:
                # 4.1 Inject Shift into Test Data
                shifted_test_df, meta = self.shift_generator.apply_shift(
                    df_test=test_df,
                    scenario=scenario,
                    severity=severity,
                    target_features=numeric_targets,
                    train_reference_df=train_df
                )

                # Transform shifted test data using frozen train-fitted preprocessor
                X_shift_df = shifted_test_df[feature_cols]
                X_shift_proc = self.preprocessor.transform(X_shift_df)

                # 4.2 Compute Distribution Distance (Baseline Test vs Shifted Test)
                # MMD with RBF Kernel and OOM mitigation
                mmd_dist, _, _ = calculate_mmd(
                    X_test_proc,
                    X_shift_proc,
                    subsample_limit=self.config.mmd_subsample_limit,
                    random_seed=self.config.random_seed
                )

                # Multivariate Sliced Wasserstein Distance
                wass_dist = calculate_wasserstein(
                    X_test_proc,
                    X_shift_proc,
                    random_seed=self.config.random_seed
                )

                # 4.3 Evaluate Frozen Baseline Models on Shifted Test Set
                shifted_metrics = {}
                abs_degradations = {}
                rel_degradations = {}
                threshold_statuses = {}
                threshold_values = {}

                for algo in self.config.algorithms:
                    model = self.trained_models[algo.value]
                    base_eval = self.baselines[algo.value]

                    s_metrics = ModelEvaluator.evaluate(model, X_shift_proc, y_test)
                    shifted_metrics[algo.value] = s_metrics

                    # Calculate Degradations
                    a_deg, r_deg = DegradationCalculator.calculate_degradations(
                        base_eval.test_metrics,
                        s_metrics
                    )
                    abs_degradations[algo.value] = a_deg
                    rel_degradations[algo.value] = r_deg

                    # Dynamic Threshold Check: Degradation(F1) > 1.5 * Std_Dev(CV)
                    f1_cv_std = base_eval.cv_scores_std.get("f1_score", 0.01)
                    f1_abs_deg = a_deg.get("f1_score", 0.0)

                    status, t_val = ReliabilityThresholdChecker.evaluate_dynamic_threshold(
                        absolute_degradation=f1_abs_deg,
                        cv_std_dev=f1_cv_std,
                        multiplier=self.config.threshold_std_multiplier
                    )
                    threshold_statuses[algo.value] = status
                    threshold_values[algo.value] = t_val

                shift_res = ShiftResult(
                    scenario=scenario,
                    severity=severity,
                    target_features=numeric_targets,
                    mmd_distance=mmd_dist,
                    wasserstein_distance=wass_dist,
                    shifted_metrics=shifted_metrics,
                    degradation_absolute=abs_degradations,
                    degradation_relative_pct=rel_degradations,
                    threshold_status=threshold_statuses,
                    threshold_value=threshold_values
                )
                self.shift_results.append(shift_res)

        # Step 5: Construct Reliability Profile
        self.reliability_profile = ReliabilityProfile(
            experiment_id=self.experiment_id,
            dataset_name=self.config.dataset_name,
            baselines=self.baselines,
            shift_results=self.shift_results
        )

        return self.reliability_profile
