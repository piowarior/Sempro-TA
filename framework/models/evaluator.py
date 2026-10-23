"""
Model Evaluation Engine for Supervised Classification.
Computes Accuracy, Precision, Recall, F1-Score, and ROC-AUC.
"""

from typing import Dict, Any, Optional
import numpy as np
from sklearn.metrics import (
    accuracy_score,
    precision_score,
    recall_score,
    f1_score,
    roc_auc_score
)
from ..config import EvaluationMetrics


class ModelEvaluator:
    """
    Evaluator for classification models on baseline and shifted datasets.
    """

    @staticmethod
    def evaluate(
        model: Any,
        X_test: np.ndarray,
        y_test: np.ndarray,
        average: str = "macro"
    ) -> EvaluationMetrics:
        """
        Evaluates a trained classifier on (X_test, y_test).
        """
        y_pred = model.predict(X_test)

        acc = float(accuracy_score(y_test, y_pred))
        prec = float(precision_score(y_test, y_pred, average=average, zero_division=0))
        rec = float(recall_score(y_test, y_pred, average=average, zero_division=0))
        f1 = float(f1_score(y_test, y_pred, average=average, zero_division=0))

        # ROC-AUC computation
        roc_auc = 0.5
        try:
            if hasattr(model, "predict_proba"):
                probs = model.predict_proba(X_test)
                n_classes = len(np.unique(y_test))
                if n_classes == 2:
                    # Binary classification: positive class prob
                    roc_auc = float(roc_auc_score(y_test, probs[:, 1]))
                elif n_classes > 2:
                    roc_auc = float(roc_auc_score(y_test, probs, multi_class="ovr", average=average))
            elif hasattr(model, "decision_function"):
                scores = model.decision_function(X_test)
                roc_auc = float(roc_auc_score(y_test, scores))
        except Exception:
            # Fallback if single class present in test batch
            roc_auc = 0.5

        return EvaluationMetrics(
            accuracy=round(acc, 4),
            precision=round(prec, 4),
            recall=round(rec, 4),
            f1_score=round(f1, 4),
            roc_auc=round(roc_auc, 4)
        )
