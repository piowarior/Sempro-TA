"""
Unit tests for ExternalModelLoader.
Validates loading user pre-trained models (.pkl / .joblib) and strict schema mismatch error handling.
"""

import os
import tempfile
import joblib
import numpy as np
import pytest
from sklearn.linear_model import LogisticRegression
from framework.models.loader import ExternalModelLoader, ModelSchemaMismatchError
from framework.pipeline import StressTestingPipeline
from framework.config import ExperimentConfig, ModelAlgorithm, ShiftScenario, ShiftSeverity
from framework.dataset.loader import generate_nutrition_dataset


def test_external_model_loading_and_pipeline():
    with tempfile.TemporaryDirectory() as tmpdir:
        model_path = os.path.join(tmpdir, "model_gizi_rf.pkl")

        # 1. Train a dummy model outside framework (e.g. nutrition model)
        df_gizi = generate_nutrition_dataset(n_samples=300, random_seed=42)
        X = df_gizi[["kalori", "protein_g", "lemak_g", "karbohidrat_g", "serat_g", "gula_g", "natrium_mg"]].to_numpy()
        y = df_gizi["status_gizi"].to_numpy()

        dummy_model = LogisticRegression(random_state=42, max_iter=200)
        dummy_model.fit(X, y)

        # Save to disk as .pkl
        joblib.dump(dummy_model, model_path)

        # 2. Test loader
        loaded_model = ExternalModelLoader.load_model(model_path)
        assert hasattr(loaded_model, "predict")

        # 3. Test integrating into pipeline
        config = ExperimentConfig(
            dataset_name="Nilai_Gizi_Evaluation",
            target_column="status_gizi",
            feature_columns=["kalori", "protein_g", "lemak_g", "karbohidrat_g", "serat_g", "gula_g", "natrium_mg"],
            external_model_path=model_path,
            algorithms=[],  # No internal model, purely test external model!
            scenarios=[ShiftScenario.MEAN_SHIFT],
            severities=[ShiftSeverity.LOW, ShiftSeverity.HIGH],
            random_seed=42
        )

        pipeline = StressTestingPipeline(config=config)
        # Drop category column so features match numeric external model
        df_numeric_gizi = df_gizi.drop(columns=["kategori_makanan"])
        profile = pipeline.run(df=df_numeric_gizi)

        assert profile is not None
        assert "model_gizi_rf" in profile.baselines
        assert len(profile.profile_df) == 2  # 2 severities for the 1 external model
        assert profile.profile_df["model"].iloc[0] == "model_gizi_rf"


def test_external_model_schema_mismatch():
    # Model trained on 3 features
    X_train = np.random.randn(50, 3)
    y_train = np.random.randint(0, 2, size=50)
    model = LogisticRegression().fit(X_train, y_train)

    # Eval data has 5 features (mismatch)
    X_eval_wrong = np.random.randn(20, 5)

    with pytest.raises(ModelSchemaMismatchError, match="Ketidakcocokan Jumlah Fitur"):
        ExternalModelLoader.validate_model_schema(model, X_eval_wrong)
