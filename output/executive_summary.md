# Ringkasan Reliability Profile: Credit_Card_Default_Benchmark
**Experiment ID:** `exp_7ea7dc87`

## 1. Performa Baseline & Ambang Batas Toleransi (5-Fold CV)
- **logistic_regression:**
  - Test F1-Score: 0.6253 | ROC-AUC: 0.7008 | Accuracy: 0.65
  - CV F1 Mean: 0.611 ± 0.0155
  - Batas Toleransi Dinamis (1.5x Std Dev CV): `0.0232`
- **xgboost:**
  - Test F1-Score: 0.5701 | ROC-AUC: 0.6495 | Accuracy: 0.7033
  - CV F1 Mean: 0.5584 ± 0.0242
  - Batas Toleransi Dinamis (1.5x Std Dev CV): `0.0363`

## 2. Analisis Korelasi Spearman Rank (Distance vs F1 Degradation)
- **logistic_regression:**
  - MMD vs F1 Degradation: rs = 0.7167 (p = 0.0298)
  - Wasserstein vs F1 Degradation: rs = 0.6667 (p = 0.0499)
- **xgboost:**
  - MMD vs F1 Degradation: rs = 0.5167 (p = 0.1544)
  - Wasserstein vs F1 Degradation: rs = 0.1833 (p = 0.6368)

## 3. Ringkasan Kejadian Pelanggaran Ambang Batas (Threshold Breaches)
- Total Kondisi Pengujian: 18
- Kondisi Melewati Ambang Batas (Breached): 8 (44.4%)