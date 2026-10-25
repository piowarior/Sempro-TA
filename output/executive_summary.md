# Laporan Eksekutif Evaluasi Ketahanan Model (Reliability Profile)
- **Schema Version:** `1.1.0`
- **Framework Version:** `1.1.0`
- **Timestamp (UTC):** `2026-10-25T02:37:17.254404+00:00`
- **Experiment ID:** `exp_657d3d35`
- **Dataset:** `Credit_Card_Default_Benchmark`

## 1. Performa Baseline & Ambang Batas Keandalan (5-Fold CV)
### Model: `logistic_regression`
- **Test Set Baseline:** F1-Score: **0.6253** | ROC-AUC: **0.7008** | Accuracy: **0.65**
- **5-Fold Cross-Validation:** F1-Score Mean: **0.611 ± 0.0155**
- **Batas Toleransi Dinamis (1.5x Std Dev CV):** `Δ_abs <= 0.0232`
### Model: `xgboost`
- **Test Set Baseline:** F1-Score: **0.5701** | ROC-AUC: **0.6495** | Accuracy: **0.7033**
- **5-Fold Cross-Validation:** F1-Score Mean: **0.5584 ± 0.0242**
- **Batas Toleransi Dinamis (1.5x Std Dev CV):** `Δ_abs <= 0.0363`

## 2. Analisis Korelasi Spearman Rank (Distance vs Degradasi Performa)
### Model: `logistic_regression`
- **MMD vs F1 Degradation (Overall):**
  - rs = `0.0786` | p-value = `0.7808` | Signifikan: **False**
- **Wasserstein vs F1 Degradation (Overall):**
  - rs = `-0.0036` | p-value = `0.9899` | Signifikan: **False**
- **Dekomposisi Per-Skenario:**
  - `mean_shift`: rs(MMD) = `0.5`, rs(Wasserstein) = `0.5`
  - `variance_shift`: rs(MMD) = `1.0`, rs(Wasserstein) = `1.0`
  - `outlier_shift`: rs(MMD) = `1.0`, rs(Wasserstein) = `1.0`
  - `categorical_shift`: rs(MMD) = `0.5`, rs(Wasserstein) = `0.5`
  - `subsampling_shift`: rs(MMD) = `-0.5`, rs(Wasserstein) = `-0.5`
### Model: `xgboost`
- **MMD vs F1 Degradation (Overall):**
  - rs = `0.0107` | p-value = `0.9698` | Signifikan: **False**
- **Wasserstein vs F1 Degradation (Overall):**
  - rs = `-0.2` | p-value = `0.4748` | Signifikan: **False**
- **Dekomposisi Per-Skenario:**
  - `mean_shift`: rs(MMD) = `1.0`, rs(Wasserstein) = `1.0`
  - `variance_shift`: rs(MMD) = `-0.5`, rs(Wasserstein) = `-0.5`
  - `outlier_shift`: rs(MMD) = `1.0`, rs(Wasserstein) = `1.0`
  - `categorical_shift`: rs(MMD) = `1.0`, rs(Wasserstein) = `1.0`
  - `subsampling_shift`: rs(MMD) = `1.0`, rs(Wasserstein) = `1.0`

## 3. Ringkasan Kejadian Pelanggaran Batas Keandalan (Threshold Breaches)
- Total Kondisi Pengujian: **30**
- Kondisi Melewati Ambang Batas (Gagal / Breached): **14 (46.7%)**
- Kondisi Aman / Dalam Toleransi: **16 (53.3%)**

## 4. Validasi Asumsi & Keterbatasan Metodologis
1. **Validitas Asumsi Covariate Shift:** Skenario A, B, dan C menggunakan gangguan sintetis terkontrol pada ruang fitur. Skenario E (Subsampling) menjamin P(Y|X) konstan secara murni melalui biased selection.
2. **Dukungan Data (Support / Positivity):** Nilai `support_overlap_pct` mencatat persentase sampel shifted yang berada di dalam rentang observasi training. Skenario ekstrapolasi tinggi (outlier) ditandai sebagai uji stres batas.
3. **Skala Jarak Statistik:** MMD (berbasis RBF kernel) dan Wasserstein Distance (Sliced Wasserstein) memiliki skala numerik dan sensitivitas berbeda; keduanya tidak dimaksudkan untuk diperbandingkan secara nilai absolut langsung.