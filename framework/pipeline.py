"""
End-to-End Stress Testing Pipeline Orchestrator (Schema 1.1.0).
Executes the full experimental workflow:
1. Data Input & Validation
2. Leakage-free Stratified Preprocessing
3. Baseline Model Training & 5-Fold Cross Validation (or Loading External Pretrained .pkl Model)
4. Controlled Shift Injection (Location, Scale, Outlier, Categorical, Subsampling)
5. Distribution Distance Calculation (MMD & Wasserstein with OOM mitigation)
6. Shifted Evaluation & Degradation Assessment
7. Dynamic Threshold Checking (1.5x CV Std Dev or Policy)
8. Reliability Profile Generation with Provenance, Support Checks, and Spearman Correlation
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
    ThresholdStatus,
    ThresholdPolicy,
    SCHEMA_VERSION
)
from .dataset.validator import DatasetValidator
from .dataset.loader import split_train_test, generate_credit_default_dataset
from .dataset.preprocessor import TabularPreprocessor
from .shift.generator import ShiftGenerator
from .distance.mmd import calculate_mmd
from .distance.wasserstein import calculate_wasserstein
from .models.baseline import BaselineTrainer
from .models.loader import ExternalModelLoader
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

        # Fit preprocessor STRICTLY on train_df (Zero Data Leakage)
        X_train_df = train_df[feature_cols]
        X_test_df = test_df[feature_cols]

        X_train_proc = self.preprocessor.fit_transform(X_train_df)
        X_test_proc = self.preprocessor.transform(X_test_df)

        # Step 3: Establish Baseline Models (Internal Training or External .pkl)
        self.trained_models = {}
        self.baselines = {}

        # 3.1 Check if external model is provided (e.g. model_gizi_rf.pkl)
        if self.config.external_model_path and os.path.exists(self.config.external_model_path):
            ext_model = ExternalModelLoader.load_model(self.config.external_model_path)
            ext_name = os.path.splitext(os.path.basename(self.config.external_model_path))[0]
            ext_eval = ExternalModelLoader.evaluate_external_baseline(
                ext_model,
                model_name=ext_name,
                X_test=X_test_proc,
                y_test=y_test,
                feature_names=self.preprocessor.transformed_feature_names,
                cv_std_dev_estimate=0.025
            )
            self.trained_models[ext_name] = ext_model
            self.baselines[ext_name] = ext_eval

        # 3.2 Train internal standard baseline models
        for algo in self.config.algorithms:
            if algo == ModelAlgorithm.EXTERNAL_PRETRAINED:
                continue
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
        categorical_targets = inspection["categorical_features"]

        for scenario in self.config.scenarios:
            # Skip categorical shift if dataset has no categorical features
            if scenario == ShiftScenario.CATEGORICAL_SHIFT and not categorical_targets:
                continue

            for severity in self.config.severities:
                # 4.1 Determine target features per scenario
                target_feats = categorical_targets if scenario == ShiftScenario.CATEGORICAL_SHIFT else numeric_targets

                # 4.2 Inject Shift into Test Data
                shifted_test_df, meta = self.shift_generator.apply_shift(
                    df_test=test_df,
                    scenario=scenario,
                    severity=severity,
                    target_features=target_feats,
                    train_reference_df=train_df
                )

                # Transform shifted test data using frozen train-fitted preprocessor
                X_shift_df = shifted_test_df[feature_cols]
                X_shift_proc = self.preprocessor.transform(X_shift_df)

                # 4.3 Compute Distribution Distance (Baseline Test vs Shifted Test)
                mmd_dist, _, _ = calculate_mmd(
                    X_test_proc,
                    X_shift_proc,
                    subsample_limit=self.config.mmd_subsample_limit,
                    random_seed=self.config.random_seed
                )

                wass_dist = calculate_wasserstein(
                    X_test_proc,
                    X_shift_proc,
                    random_seed=self.config.random_seed
                )

                # 4.4 Evaluate Frozen Baseline Models on Shifted Test Set
                shifted_metrics = {}
                abs_degradations = {}
                rel_degradations = {}
                threshold_statuses = {}
                threshold_values = {}

                for m_name, model in self.trained_models.items():
                    base_eval = self.baselines[m_name]

                    s_metrics = ModelEvaluator.evaluate(model, X_shift_proc, y_test)
                    shifted_metrics[m_name] = s_metrics

                    # Calculate Degradations
                    a_deg, r_deg = DegradationCalculator.calculate_degradations(
                        base_eval.test_metrics,
                        s_metrics
                    )
                    abs_degradations[m_name] = a_deg
                    rel_degradations[m_name] = r_deg

                    # Reliability Threshold Check
                    f1_abs_deg = a_deg.get("f1_score", 0.0)

                    if self.config.threshold_policy == ThresholdPolicy.DYNAMIC_CV_STD:
                        f1_cv_std = base_eval.cv_scores_std.get("f1_score", 0.01)
                        status, t_val = ReliabilityThresholdChecker.evaluate_dynamic_threshold(
                            absolute_degradation=f1_abs_deg,
                            cv_std_dev=f1_cv_std,
                            multiplier=self.config.threshold_std_multiplier
                        )
                    elif self.config.threshold_policy == ThresholdPolicy.MIN_PERFORMANCE:
                        limit = self.config.static_min_f1 if self.config.static_min_f1 is not None else 0.60
                        t_val = limit
                        status = ThresholdStatus.SAFE if s_metrics.f1_score >= limit else ThresholdStatus.BREACHED
                    else:  # MAX_DEGRADATION
                        limit = self.config.static_max_degradation if self.config.static_max_degradation is not None else 0.10
                        t_val = limit
                        status = ThresholdStatus.SAFE if f1_abs_deg <= limit else ThresholdStatus.BREACHED

                    threshold_statuses[m_name] = status
                    threshold_values[m_name] = t_val

                shift_res = ShiftResult(
                    scenario=scenario,
                    severity=severity,
                    target_features=target_feats,
                    mmd_distance=mmd_dist,
                    wasserstein_distance=wass_dist,
                    shifted_metrics=shifted_metrics,
                    degradation_absolute=abs_degradations,
                    degradation_relative_pct=rel_degradations,
                    threshold_status=threshold_statuses,
                    threshold_value=threshold_values,
                    support_overlap_pct=meta.get("support_overlap_pct", 100.0),
                    shift_validity_type=meta.get("shift_validity_type", "synthetic_perturbation")
                )
                self.shift_results.append(shift_res)

        # Step 5: Construct Reliability Profile with Full Provenance
        config_meta = {
            "schema_version": SCHEMA_VERSION,
            "random_seed": self.config.random_seed,
            "test_size": self.config.test_size,
            "cv_folds": self.config.cv_folds,
            "threshold_multiplier": self.config.threshold_std_multiplier,
            "threshold_policy": self.config.threshold_policy.value,
            "models_evaluated": list(self.trained_models.keys()),
            "scenarios_evaluated": [s.value for s in self.config.scenarios],
            "severities_evaluated": [s.value for s in self.config.severities]
        }
        dataset_meta = {
            "dataset_name": self.config.dataset_name,
            "n_rows": inspection["n_rows"],
            "n_features": inspection["n_features"],
            "target_column": self.config.target_column,
            "class_distribution": inspection["class_distribution"],
            "numeric_features": inspection["numeric_features"],
            "categorical_features": inspection["categorical_features"],
        }

        self.reliability_profile = ReliabilityProfile(
            experiment_id=self.experiment_id,
            dataset_name=self.config.dataset_name,
            baselines=self.baselines,
            shift_results=self.shift_results,
            config_metadata=config_meta,
            dataset_metadata=dataset_meta
        )

        return self.reliability_profile
