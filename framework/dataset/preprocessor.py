"""
Tabular Data Preprocessor.
Guarantees zero data leakage: Imputers, Scalers, and Encoders are fitted STRICTLY
on training data and applied identically to test data and shifted test data.
"""

from typing import List, Optional, Tuple, Dict, Any
import numpy as np
import pandas as pd
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.impute import SimpleImputer


class TabularPreprocessor:
    """
    Leakage-free Preprocessor for mixed numeric and categorical tabular data.
    """

    def __init__(
        self,
        numeric_features: Optional[List[str]] = None,
        categorical_features: Optional[List[str]] = None,
        scale_numeric: bool = True,
    ):
        self.numeric_features = numeric_features or []
        self.categorical_features = categorical_features or []
        self.scale_numeric = scale_numeric

        # Transformers
        self.num_imputer = SimpleImputer(strategy="median")
        self.scaler = StandardScaler() if scale_numeric else None
        self.cat_imputer = SimpleImputer(strategy="most_frequent")
        self.encoder = OneHotEncoder(handle_unknown="ignore", sparse_output=False)

        self.is_fitted = False
        self.transformed_feature_names: List[str] = []

    def fit(self, X: pd.DataFrame) -> "TabularPreprocessor":
        """
        Fits transformers strictly on training data X.
        """
        # If feature lists were not provided, infer from X
        if not self.numeric_features and not self.categorical_features:
            self.numeric_features = [
                c for c in X.columns if pd.api.types.is_numeric_dtype(X[c])
            ]
            self.categorical_features = [
                c for c in X.columns if c not in self.numeric_features
            ]

        # Fit numeric
        if self.numeric_features:
            num_data = X[self.numeric_features]
            imputed_num = self.num_imputer.fit_transform(num_data)
            if self.scaler:
                self.scaler.fit(imputed_num)

        # Fit categorical
        if self.categorical_features:
            cat_data = X[self.categorical_features].astype(str)
            imputed_cat = self.cat_imputer.fit_transform(cat_data)
            self.encoder.fit(imputed_cat)

        # Assemble feature names
        names = []
        if self.numeric_features:
            names.extend(self.numeric_features)
        if self.categorical_features:
            cat_names = self.encoder.get_feature_names_out(self.categorical_features)
            names.extend(list(cat_names))
        self.transformed_feature_names = names

        self.is_fitted = True
        return self

    def transform(self, X: pd.DataFrame) -> np.ndarray:
        """
        Transforms test or shifted data using parameters learned from training data.
        """
        if not self.is_fitted:
            raise RuntimeError("Preprocessor must be fitted on training data before transforming.")

        arrays_to_concat = []

        if self.numeric_features:
            num_data = X[self.numeric_features]
            imputed_num = self.num_imputer.transform(num_data)
            if self.scaler:
                scaled_num = self.scaler.transform(imputed_num)
            else:
                scaled_num = imputed_num
            arrays_to_concat.append(scaled_num)

        if self.categorical_features:
            cat_data = X[self.categorical_features].astype(str)
            imputed_cat = self.cat_imputer.transform(cat_data)
            encoded_cat = self.encoder.transform(imputed_cat)
            arrays_to_concat.append(encoded_cat)

        if not arrays_to_concat:
            return np.empty((len(X), 0))

        return np.hstack(arrays_to_concat)

    def fit_transform(self, X: pd.DataFrame) -> np.ndarray:
        return self.fit(X).transform(X)
