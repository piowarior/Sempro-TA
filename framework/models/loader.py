"""
External Pre-trained Model Loader with Schema & Serialization Validation.
Guarantees robust contracts for user-provided models (.pkl / .joblib):
- Verifies feature count (n_features_in_)
- Verifies feature names and ordering (feature_names_in_)
- Performs dry-run prediction sanity check
- Throws clear ModelSchemaMismatchError instead of silent unhandled crashes.
"""

import os
import pickle
from typing import Any, Tuple, Optional, List
import joblib
import numpy as np
from ..config import BaselineEvaluation
from .evaluator import ModelEvaluator


class ModelSchemaMismatchError(Exception):
    """Raised when an external model's expected input schema differs from evaluation data."""
    pass


class ExternalModelLoader:
    """
    Loads and validates external scikit-learn or XGBoost models from disk.
    """

    @staticmethod
    def load_model(model_path: str) -> Any:
        """
        Loads a serialized model (.pkl or .joblib) with sanity check.
        """
        if not os.path.exists(model_path):
            raise FileNotFoundError(f"Model file not found at: {model_path}")

        try:
            model = joblib.load(model_path)
        except Exception:
            with open(model_path, "rb") as f:
                model = pickle.load(f)

        if not hasattr(model, "predict"):
            raise ValueError(
                f"Loaded object from {model_path} does not implement the required 'predict' method."
            )

        return model

    @staticmethod
    def validate_model_schema(
        model: Any,
        X_eval: np.ndarray,
        feature_names: Optional[List[str]] = None
    ) -> None:
        """
        Validates feature dimensions, column names, and runs a 1-sample dry run.
        """
        n_features_eval = X_eval.shape[1]

        # 1. Check feature count
        if hasattr(model, "n_features_in_"):
            expected_n = model.n_features_in_
            if n_features_eval != expected_n:
                raise ModelSchemaMismatchError(
                    f"Ketidakcocokan Jumlah Fitur: Model eksternal diekspektasikan menerima {expected_n} fitur, "
                    f"tetapi data evaluasi yang masuk memiliki {n_features_eval} fitur."
                )

        # 2. Check feature names if available
        if hasattr(model, "feature_names_in_") and feature_names:
            expected_names = list(model.feature_names_in_)
            if len(expected_names) == len(feature_names):
                diffs = [
                    f"posisi {i}: model '{expected_names[i]}' vs data '{feature_names[i]}'"
                    for i in range(len(expected_names))
                    if expected_names[i] != feature_names[i]
                ]
                if diffs:
                    raise ModelSchemaMismatchError(
                        f"Ketidakcocokan Nama/Urutan Fitur antara model dan data: {'; '.join(diffs[:3])}"
                    )

        # 3. Dry-run test prediction on first row
        try:
            sample_pred = model.predict(X_eval[:1])
            if sample_pred is None:
                raise ValueError("Model predict returned None.")
        except Exception as e:
            raise ModelSchemaMismatchError(
                f"Uji coba inferensi (dry-run) gagal dieksekusi pada 1 sampel pertama: {str(e)}"
            )

    @staticmethod
    def evaluate_external_baseline(
        model: Any,
        model_name: str,
        X_test: np.ndarray,
        y_test: np.ndarray,
        feature_names: Optional[List[str]] = None,
        cv_std_dev_estimate: float = 0.025
    ) -> BaselineEvaluation:
        """
        Validates schema and evaluates pre-trained external model on unshifted baseline test data.
        """
        # Validate schema first
        ExternalModelLoader.validate_model_schema(model, X_test, feature_names=feature_names)

        test_metrics = ModelEvaluator.evaluate(model, X_test, y_test)

        baseline_eval = BaselineEvaluation(
            model_name=model_name,
            cv_scores_mean={
                "accuracy": test_metrics.accuracy,
                "precision": test_metrics.precision,
                "recall": test_metrics.recall,
                "f1_score": test_metrics.f1_score,
                "roc_auc": test_metrics.roc_auc,
            },
            cv_scores_std={
                "accuracy": cv_std_dev_estimate,
                "precision": cv_std_dev_estimate,
                "recall": cv_std_dev_estimate,
                "f1_score": cv_std_dev_estimate,
                "roc_auc": cv_std_dev_estimate,
            },
            test_metrics=test_metrics,
            fit_time_seconds=0.0,
            is_external_model=True
        )

        return baseline_eval
