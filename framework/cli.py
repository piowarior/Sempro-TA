"""
Command Line Interface (CLI) for ML Reliability Stress Testing Framework.
Provides commands analogous to 'create-next-app':
  - init: Scaffolds a new stress testing project folder with templates & sample data
  - run: Runs controlled stress testing experiments from config/CLI
  - ui: Launches the interactive 7-page Streamlit dashboard
"""

import os
import sys
import argparse
import shutil
import pandas as pd

from framework.config import (
    ExperimentConfig,
    ShiftScenario,
    ShiftSeverity,
    ModelAlgorithm
)
from framework.pipeline import StressTestingPipeline
from framework.dataset.loader import generate_credit_default_dataset


def cmd_init(args):
    """
    Scaffolds a clean, standardized research project directory.
    Analogous to 'npx create-next-app'.
    """
    project_dir = args.project_name or "projek_stress_test"
    print(f"📦 Membuat struktur projek framework baru di: ./{project_dir}")

    # Create folder structure
    dirs = [
        project_dir,
        os.path.join(project_dir, "data"),
        os.path.join(project_dir, "config"),
        os.path.join(project_dir, "output"),
    ]
    for d in dirs:
        os.makedirs(d, exist_ok=True)

    # 1. Provide sample dataset in data/
    sample_data_path = os.path.join(project_dir, "data", "credit_card_sample.csv")
    df = generate_credit_default_dataset(n_samples=1500, random_seed=42)
    df.to_csv(sample_data_path, index=False)
    print(f"  ✅ Dataset contoh disimpan di: {sample_data_path}")

    # 2. Provide human-readable config JSON in config/
    sample_config_path = os.path.join(project_dir, "config", "experiment_config.json")
    config_content = """{
  "dataset_path": "data/credit_card_sample.csv",
  "target_column": "default",
  "test_size": 0.20,
  "random_seed": 42,
  "cv_folds": 5,
  "threshold_multiplier": 1.5,
  "algorithms": ["logistic_regression", "xgboost"],
  "scenarios": ["mean_shift", "variance_shift", "outlier_shift"],
  "severities": [0.25, 0.50, 1.00]
}"""
    with open(sample_config_path, "w", encoding="utf-8") as f:
        f.write(config_content)
    print(f"  ✅ File konfigurasi disimpan di: {sample_config_path}")

    # 3. Provide README instructions in project folder
    readme_path = os.path.join(project_dir, "CARA_PAKAI.txt")
    instructions = f"""======================================================================
PANDUAN PENGGUNAAN PROJEK STRESS TESTING ML
======================================================================
1. TARO DATASET KAMU:
   - Letakkan file CSV kamu di folder: data/
   - Atau gunakan data contoh yang sudah ada: data/credit_card_sample.csv

2. ATUR KONFIGURASI:
   - Buka file: config/experiment_config.json
   - Ubah "target_column" sesuai nama kolom label klasifikasi kamu.

3. CARA MENJALANKAN (PILIH SALAH SATU):
   A) MODE WEB INTERAKTIF (Paling Direkomendasikan):
      Buka terminal dan jalankan:
      streamlit run app.py

   B) MODE TERMINAL OTOMATIS (Headless):
      python run_experiments.py

4. MELIHAT HASIL:
   - Tabel profil reliabilitas: output/reliability_profile.csv
   - Ringkasan riset: output/executive_summary.md
   - Gambar visualisasi: output/*.png
======================================================================
"""
    with open(readme_path, "w", encoding="utf-8") as f:
        f.write(instructions)
    print(f"  ✅ Panduan langkah demi langkah disimpan di: {readme_path}")

    print("\n✨ Projek siap digunakan! Langkah berikutnya:")
    print(f"   cd {project_dir}")
    print("   streamlit run ../app.py   (atau jalankan UI di browser)")
    print("======================================================================")


def cmd_run(args):
    """
    Executes benchmark runner.
    """
    import run_experiments
    run_experiments.run_full_benchmark(output_dir=args.output_dir)


def cmd_ui(args):
    """
    Launches Streamlit interactive application.
    """
    import subprocess
    print("🚀 Meluncurkan Antarmuka Web Interaktif Streamlit...")
    subprocess.run([sys.executable, "-m", "streamlit", "run", "app.py"])


def main():
    parser = argparse.ArgumentParser(
        description="Framework Stress Testing untuk Evaluasi Ketahanan Model Machine Learning"
    )
    subparsers = parser.add_subparsers(dest="command", help="Perintah yang tersedia")

    # Command: init
    init_parser = subparsers.add_parser("init", help="Buat struktur folder projek eksperimen baru (seperti create-next-app)")
    init_parser.add_argument("project_name", nargs="?", default="projek_stress_test", help="Nama folder projek baru")

    # Command: run
    run_parser = subparsers.add_parser("run", help="Jalankan eksperimen stress testing otomatis via terminal")
    run_parser.add_argument("--output-dir", default="output", help="Folder penyimpanan hasil")

    # Command: ui
    ui_parser = subparsers.add_parser("ui", help="Buka dashboard web interaktif Streamlit di browser")

    args = parser.parse_args()

    if args.command == "init":
        cmd_init(args)
    elif args.command == "run":
        cmd_run(args)
    elif args.command == "ui":
        cmd_ui(args)
    else:
        parser.print_help()


if __name__ == "__main__":
    main()
