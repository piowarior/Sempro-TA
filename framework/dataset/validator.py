"""
Dataset Validation Module.
Validates tabular structure, data types, target column, missingness, and sample sufficiency.
"""

from typing import Dict, List, Tuple, Any
import numpy as np
import pandas as pd


class DatasetValidationError(Exception):
    """Raised when dataset fails sanity or validation checks."""
    pass


class DatasetValidator:
    """
    Validates tabular datasets for supervised classification stress testing.
    """

    @staticmethod
    def inspect(df: pd.DataFrame, target_column: str) -> Dict[str, Any]:
        """
        Inspect dataset metadata, column types, and distributions.
        """
        if df is None or df.empty:
            raise DatasetValidationError("Dataset is empty or None.")

        if target_column not in df.columns:
            raise DatasetValidationError(
                f"Target column '{target_column}' not found in dataset columns: {list(df.columns)}"
            )

        n_rows, n_cols = df.shape
        if n_rows < 50:
            raise DatasetValidationError(
                f"Dataset contains only {n_rows} rows. Minimum 50 rows required for reliable evaluation."
            )

        features = [c for c in df.columns if c != target_column]
        if len(features) < 1:
            raise DatasetValidationError("Dataset must contain at least one feature column besides target.")

        numeric_features = [
            col for col in features if pd.api.types.is_numeric_dtype(df[col])
        ]
        categorical_features = [
            col for col in features if col not in numeric_features
        ]

        target_series = df[target_column]
        class_counts = target_series.value_counts(dropna=False).to_dict()
        n_classes = len(class_counts)

        if n_classes < 2:
            raise DatasetValidationError(
                f"Target '{target_column}' must have at least 2 distinct classes, found {n_classes}."
            )

        missing_counts = df.isnull().sum().to_dict()
        total_missing = sum(missing_counts.values())

        return {
            "n_rows": n_rows,
            "n_features": len(features),
            "feature_names": features,
            "numeric_features": numeric_features,
            "categorical_features": categorical_features,
            "target_column": target_column,
            "n_classes": n_classes,
            "class_distribution": class_counts,
            "total_missing_values": int(total_missing),
            "missing_per_column": missing_counts,
        }
