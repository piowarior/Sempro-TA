"""
Dataset Loader and Benchmark Generator.
Provides realistic tabular datasets:
1. Credit Card Default benchmark
2. Nutrition Classification benchmark ('nilai-gizi')
and stratified train-test splitting with zero data leakage.
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
    income = rng.lognormal(mean=10.2, sigma=0.5, size=n_samples)
    credit_amount = rng.normal(loc=15000, scale=6000, size=n_samples).clip(1000, 50000)
    debt_ratio = rng.beta(a=2, b=5, size=n_samples) * 1.5
    employment_years = rng.exponential(scale=5, size=n_samples).clip(0, 40)
    credit_score = rng.normal(loc=650, scale=70, size=n_samples).clip(300, 850)

    education_choices = ["High School", "Bachelor", "Master", "Doctorate"]
    education = rng.choice(education_choices, size=n_samples, p=[0.35, 0.45, 0.15, 0.05])

    housing_choices = ["Rent", "Own", "Mortgage"]
    housing = rng.choice(housing_choices, size=n_samples, p=[0.40, 0.35, 0.25])

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


def generate_nutrition_dataset(n_samples: int = 2000, random_seed: int = 42) -> pd.DataFrame:
    """
    Generates a realistic nutrition evaluation dataset ('nilai-gizi').
    Features: kalori, protein_g, lemak_g, karbohidrat_g, serat_g, gula_g, natrium_mg, kategori_makanan.
    Target: status_gizi (1: Sehat / Memenuhi Standar Gizi, 0: Kurang Sehat / Berisiko).
    """
    rng = np.random.default_rng(random_seed)

    kalori = rng.normal(loc=250, scale=120, size=n_samples).clip(20, 800)
    protein = rng.normal(loc=12, scale=8, size=n_samples).clip(0.5, 50)
    lemak = rng.normal(loc=10, scale=7, size=n_samples).clip(0.1, 45)
    karbo = rng.normal(loc=30, scale=18, size=n_samples).clip(1, 100)
    serat = rng.exponential(scale=3, size=n_samples).clip(0, 20)
    gula = rng.exponential(scale=10, size=n_samples).clip(0, 60)
    natrium = rng.normal(loc=400, scale=250, size=n_samples).clip(10, 1500)

    kategori_choices = ["Makanan Pokok", "Lauk Pauk", "Sayuran", "Buah", "Camilan"]
    kategori = rng.choice(kategori_choices, size=n_samples, p=[0.25, 0.30, 0.20, 0.15, 0.10])

    # Latent nutritional health index
    score = (
        0.08 * protein
        + 0.15 * serat
        - 0.05 * gula
        - 0.04 * lemak
        - 0.002 * natrium
        - 0.002 * (kalori - 200)
        + (kategori == "Sayuran") * 0.8
        + (kategori == "Buah") * 0.6
        - (kategori == "Camilan") * 0.7
    )
    prob_sehat = 1.0 / (1.0 + np.exp(-score))
    status_gizi = (rng.uniform(size=n_samples) < prob_sehat).astype(int)

    df = pd.DataFrame({
        "kalori": np.round(kalori, 1),
        "protein_g": np.round(protein, 1),
        "lemak_g": np.round(lemak, 1),
        "karbohidrat_g": np.round(karbo, 1),
        "serat_g": np.round(serat, 1),
        "gula_g": np.round(gula, 1),
        "natrium_mg": np.round(natrium, 1),
        "kategori_makanan": kategori,
        "status_gizi": status_gizi
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
