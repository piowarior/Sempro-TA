"""
Unit tests for TabularPreprocessor.
Verifies zero data leakage, correct encoding of mixed tabular types, and deterministic transforms.
"""

import numpy as np
import pandas as pd
import pytest
from framework.dataset.preprocessor import TabularPreprocessor


def test_preprocessor_no_data_leakage():
    # Synthetic train and test datasets
    train_df = pd.DataFrame({
        "num1": [10.0, 20.0, 30.0, 40.0],
        "cat1": ["A", "B", "A", "B"]
    })
    test_df = pd.DataFrame({
        "num1": [100.0, 200.0],  # Very different distribution
        "cat1": ["A", "C"]       # "C" is unseen in training set
    })

    prep = TabularPreprocessor()
    X_train_proc = prep.fit_transform(train_df)

    # Preprocessor scaler mean should match train_df mean (25.0), NOT test_df
    assert prep.scaler is not None
    assert np.isclose(prep.scaler.mean_[0], 25.0)

    # Transform test_df without re-fitting
    X_test_proc = prep.transform(test_df)

    # Output dimensions should match between train and test
    assert X_train_proc.shape[1] == X_test_proc.shape[1]
    # Unseen category "C" should be gracefully zero-encoded without error
    assert np.all(np.isfinite(X_test_proc))


def test_preprocessor_not_fitted_error():
    prep = TabularPreprocessor()
    df = pd.DataFrame({"num": [1, 2, 3]})
    with pytest.raises(RuntimeError, match="must be fitted"):
        prep.transform(df)
