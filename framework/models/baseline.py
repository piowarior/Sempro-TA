"""
Baseline Model Trainer and Cross-Validation Evaluator.
Implements Logistic Regression and XGBoost with 5-Fold Stratified Cross Validation.
Calculates CV Mean and Standard Deviation to establish the dynamic reliability threshold.
"""

import time
from typing import Dict, List, Tuple, Any
import numpy as np
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import StratifiedKFold
from ..config import ModelAlgorithm, BaselineEvaluation, EvaluationMetrics
from .evaluator import ModelEvaluator

# Try importing XGBoost; provide standard gradient boosting fallback if needed
try:
    from xgboost import XGBClassifier
    XGBOOST_AVAILABLE = True
except ImportError:
    from sklearn.ensemble import HistGradientBoostingClassifier as XGBClassifier
    XGBOOST_AVAILABLE = False


class BaselineTrainer:
    """
    Trains baseline models and computes 5-Fold Cross Validation statistics.
    """

    def __init__(self, random_seed: int = 42, cv_folds: int = 5):
        self.random_seed = random_seed
        self.cv_folds = cv_folds

    def _get_model_instance(self, algorithm: ModelAlgorithm):
        if algorithm == ModelAlgorithm.LOGISTIC_REGRESSION:
            return LogisticRegression(
                random_state=self.random_seed,
                max_iter=1000,
                class_weight="balanced"
            )
        elif algorithm == ModelAlgorithm.XGBOOST:
            if XGBOOST_AVAILABLE:
                return XGBClassifier(
                    random_state=self.random_seed,
                    eval_metric="logloss",
                    n_estimators=100,
                    learning_rate=0.1,
                    max_depth=5
                )
            else:
                return XGBClassifier(
                    random_state=self.random_seed,
                    max_iter=100
                )
        else:
            raise ValueError(f"Unsupported algorithm: {algorithm}")

    def train_and_evaluate(
        self,
        algorithm: ModelAlgorithm,
        X_train: np.ndarray,
        y_train: np.ndarray,
        X_test: np.ndarray,
        y_test: np.ndarray
    ) -> Tuple[Any, BaselineEvaluation]:
        """
        Executes:
        1. 5-Fold Stratified CV on (X_train, y_train) to estimate CV mean & std dev.
        2. Final fit on full (X_train, y_train).
        3. Baseline evaluation on unshifted (X_test, y_test).
        """
        skf = StratifiedKFold(n_splits=self.cv_folds, shuffle=True, random_state=self.random_seed)

        cv_metrics: Dict[str, List[float]] = {
            "accuracy": [],
            "precision": [],
            "recall": [],
            "f1_score": [],
            "roc_auc": []
        }

        # 1. 5-Fold CV
        for train_idx, val_idx in skf.split(X_train, y_train):
            fold_model = self._get_model_instance(algorithm)
            fold_model.fit(X_train[train_idx], y_train[train_idx])
            metrics = ModelEvaluator.evaluate(fold_model, X_train[val_idx], y_train[val_idx])

            cv_metrics["accuracy"].append(metrics.accuracy)
            cv_metrics["precision"].append(metrics.precision)
            cv_metrics["recall"].append(metrics.recall)
            cv_metrics["f1_score"].append(metrics.f1_score)
            cv_metrics["roc_auc"].append(metrics.roc_auc)

        cv_mean = {k: float(np.mean(v)) for k, v in cv_metrics.items()}
        cv_std = {k: float(np.std(v, ddof=1)) if len(v) > 1 else 0.0 for k, v in cv_metrics.items()}

        # 2. Final training on full train set
        final_model = self._get_model_instance(algorithm)
        start_time = time.time()
        final_model.fit(X_train, y_train)
        fit_time = time.time() - start_time

        # 3. Baseline test set evaluation
        test_metrics = ModelEvaluator.evaluate(final_model, X_test, y_test)

        baseline_eval = BaselineEvaluation(
            model_name=algorithm.value,
            cv_scores_mean={k: round(v, 4) for k, v in cv_mean.items()},
            cv_scores_std={k: round(v, 4) for k, v in cv_std.items()},
            test_metrics=test_metrics,
            fit_time_seconds=round(fit_time, 4)
        )

        return final_model, baseline_eval
