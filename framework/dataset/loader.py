"""
Dataset Loader and Benchmark Generator.
Provides realistic tabular datasets (Credit Default / German Credit benchmark)
and stratified train-test splitting with fixed random seed.
"""

from typing import Tuple, Optional
import numpy as np
import pandas as pd
from sklearn.model_selection import train_test_split


def generate_credit_default_dataset(n_samples: int = 3000, random_seed: int = 42) -> pd.DataFrame:
    """
    Generates a realistic synthetic Credit Card Default benchmark dataset.
    Features include both numeric and categorical variables with realistic correlations
    to default status, serving as a clean, reproducible testbed.
    """
    rng = np.random.default_rng(random_seed)

    age = rng.integers(21, 65, size=n_samples)
    income = rng.lognormal(mean=10.2, sigma=0.5, size=n_samples)  # Log-normal realistic income
    credit_amount = rng.normal(loc=15000, scale=6000, size=n_samples).clip(1000, 50000)
    debt_ratio = rng.beta(a=2, b=5, size=n_samples) * 1.5  # Debt to income
    employment_years = rng.exponential(scale=5, size=n_samples).clip(0, 40)
    credit_score = rng.normal(loc=650, scale=70, size=n_samples).clip(300, 850)

    # Categorical features
    education_choices = ["High School", "Bachelor", "Master", "Doctorate"]
    education = rng.choice(education_choices, size=n_samples, p=[0.35, 0.45, 0.15, 0.05])

    housing_choices = ["Rent", "Own", "Mortgage"]
    housing = rng.choice(housing_choices, size=n_samples, p=[0.40, 0.35, 0.25])

    # Latent probability of default: P(Y=1|X)
    # Higher debt_ratio, lower credit_score, lower income -> higher probability of default
    z = (
        - 1.5
        + 1.8 * debt_ratio
        - 0.006 * (credit_score - 600)
        - 0.00003 * (income - 30000)
        - 0.03 * employment_years
        + (housing == "Rent") * 0.3
        + (education == "High School") * 0.2
    )
    prob_default = 1.0 / (1.0 + np.exp(-z))
    default_label = (rng.uniform(size=n_samples) < prob_default).astype(int)

    df = pd.DataFrame({
        "age": age,
        "income": np.round(income, 2),
        "credit_amount": np.round(credit_amount, 2),
        "debt_ratio": np.round(debt_ratio, 4),
        "employment_years": np.round(employment_years, 1),
        "credit_score": np.round(credit_score, 0),
        "education": education,
        "housing": housing,
        "default": default_label
    })

    return df


def split_train_test(
    df: pd.DataFrame,
    target_column: str,
    test_size: float = 0.2,
    random_seed: int = 42
) -> Tuple[pd.DataFrame, pd.DataFrame]:
    """
    Stratified split to prevent data leakage and maintain target class distribution.
    """
    train_df, test_df = train_test_split(
        df,
        test_size=test_size,
        random_state=random_seed,
        stratify=df[target_column]
    )
    return train_df.reset_index(drop=True), test_df.reset_index(drop=True)
