"""
Framework Configuration and Enums for ML Reliability Stress Testing.
Defines types, experimental constants, data structures, thresholds, and schema versioning.
"""

from dataclasses import dataclass, field
from datetime import datetime, timezone
from enum import Enum
from typing import Dict, List, Optional, Any


SCHEMA_VERSION = "1.1.0"
FRAMEWORK_VERSION = "1.1.0"


class ShiftType(str, Enum):
    COVARIATE_SHIFT = "covariate_shift"
    LABEL_SHIFT = "label_shift"      # Modul non-aktif / rencana pengembangan berikutnya
    CONCEPT_SHIFT = "concept_shift"  # Modul non-aktif / rencana pengembangan berikutnya


class ShiftScenario(str, Enum):
    MEAN_SHIFT = "mean_shift"                  # Skenario A: Feature Location Shift
    VARIANCE_SHIFT = "variance_shift"          # Skenario B: Feature Scale/Variance Shift
    OUTLIER_SHIFT = "outlier_shift"            # Skenario C: Feature Shape / Outlier Ingestion
    CATEGORICAL_SHIFT = "categorical_shift"    # Skenario D: Categorical Proportion Shift
    SUBSAMPLING_SHIFT = "subsampling_shift"    # Skenario E: Pure Covariate Shift via Biased Selection


class ShiftSeverity(float, Enum):
    LOW = 0.25     # Rendah (0.25)
    MEDIUM = 0.50  # Sedang (0.50)
    HIGH = 1.00    # Tinggi (1.00)


class ModelAlgorithm(str, Enum):
    LOGISTIC_REGRESSION = "logistic_regression"
    XGBOOST = "xgboost"
    EXTERNAL_PRETRAINED = "external_pretrained"  # Model buatan pengguna (.pkl / .joblib)


class DistanceMethod(str, Enum):
    MMD_RBF = "mmd_rbf"
    WASSERSTEIN = "wasserstein"


class ThresholdPolicy(str, Enum):
    DYNAMIC_CV_STD = "dynamic_cv_std"    # Batas Dinamis: Penurunan > 1.5 x Std Dev CV
    MIN_PERFORMANCE = "min_performance"  # Batas Statis: Nilai performa minimum
    MAX_DEGRADATION = "max_degradation"  # Batas Statis: Penurunan absolut maksimum


class ThresholdStatus(str, Enum):
    SAFE = "Aman / Dalam Batas Toleransi"
    BREACHED = "Melewati Ambang Batas (Model Gagal / Berisiko)"


class RunStatus(str, Enum):
    SUCCESS = "SUCCESS"
    WARNING = "WARNING"
    FAILED = "FAILED"


@dataclass
class ExperimentConfig:
    dataset_name: str
    target_column: str
    feature_columns: List[str] = field(default_factory=list)
    test_size: float = 0.20
    random_seed: int = 42
    seeds: List[int] = field(default_factory=lambda: [42])  # Multi-seed support for consistency
    algorithms: List[ModelAlgorithm] = field(
        default_factory=lambda: [ModelAlgorithm.LOGISTIC_REGRESSION, ModelAlgorithm.XGBOOST]
    )
    scenarios: List[ShiftScenario] = field(
        default_factory=lambda: [
            ShiftScenario.MEAN_SHIFT,
            ShiftScenario.VARIANCE_SHIFT,
            ShiftScenario.OUTLIER_SHIFT,
        ]
    )
    severities: List[ShiftSeverity] = field(
        default_factory=lambda: [
            ShiftSeverity.LOW,
            ShiftSeverity.MEDIUM,
            ShiftSeverity.HIGH,
        ]
    )
    cv_folds: int = 5
    threshold_std_multiplier: float = 1.5
    mmd_subsample_limit: int = 5000  # Subsampling OOM mitigation
    external_model_path: Optional[str] = None  # Jalur file .pkl model pre-trained pengguna
    threshold_policy: ThresholdPolicy = ThresholdPolicy.DYNAMIC_CV_STD
    static_min_f1: Optional[float] = None
    static_max_degradation: Optional[float] = None


@dataclass
class EvaluationMetrics:
    accuracy: float
    precision: float
    recall: float
    f1_score: float
    roc_auc: float


@dataclass
class BaselineEvaluation:
    model_name: str
    cv_scores_mean: Dict[str, float]
    cv_scores_std: Dict[str, float]
    test_metrics: EvaluationMetrics
    fit_time_seconds: float = 0.0
    is_external_model: bool = False


@dataclass
class ShiftResult:
    scenario: ShiftScenario
    severity: ShiftSeverity
    target_features: List[str]
    mmd_distance: float
    wasserstein_distance: float
    shifted_metrics: Dict[str, EvaluationMetrics]  # model_name -> metrics
    degradation_absolute: Dict[str, Dict[str, float]]  # model_name -> {metric: abs_deg}
    degradation_relative_pct: Dict[str, Dict[str, float]]  # model_name -> {metric: rel_deg%}
    threshold_status: Dict[str, ThresholdStatus]  # model_name -> status
    threshold_value: Dict[str, float]  # model_name -> threshold value
    support_overlap_pct: float = 100.0  # % sample shifted yang berada di dalam support train
    shift_validity_type: str = "synthetic_perturbation"  # or pure_covariate_shift
