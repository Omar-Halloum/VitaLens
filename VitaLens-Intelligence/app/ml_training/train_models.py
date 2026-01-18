"""
Trains XGBoost models for:
1. Type 2 Diabetes
2. Heart Disease
3. Hypertension
4. Kidney Disease

Outputs:
- models/{risk_name}/model.json (The trained model)
- models/{risk_name}/features.json (The input mapping for the app)
"""

import pandas as pd
import numpy as np
import xgboost as xgb
from sklearn.model_selection import train_test_split
from sklearn.metrics import roc_auc_score
from pathlib import Path
import json
import warnings

warnings.filterwarnings('ignore')

BASE_DIR = Path(__file__).parent
DATA_PATH = BASE_DIR / 'dataset' / 'vitalens_health_training_data.csv'
MODELS_DIR = BASE_DIR / 'models'

RISK_CONFIG = {
    "diabetes": {
        "target": "DIQ010",
        "features": [
            'RIDAGEYR', 'RIAGENDR', 'BMXBMI', 'BMXWAIST', 'avg_systolic',
            'LBXGH', 'LBXGLU', 'LBDHDD', 'LBXTR', 
            'PAD675', 'PAD660', 'SLD010H'
        ]
    },
    "heart_disease": {
        "target": "MCQ160C",
        "features": [
            'RIDAGEYR', 'RIAGENDR', 'SMQ020', 'LBDLDL', 'LBDHDD', 'LBXTR',
            'avg_systolic', 'avg_diastolic', 'BMXBMI', 'ALQ130'
        ]
    },
    "hypertension": {
        "target": "BPQ020",
        "features": [
            'RIDAGEYR', 'avg_systolic', 'avg_diastolic', 'BMXBMI', 
            'BMXWAIST', 'ALQ130', 'LBXSUA', 
            'PAD675', 'PAD660' 
        ]
    },
    "kidney_disease": {
        "target": "KIQ022",
        "features": [
            'RIDAGEYR', 'LBXSCR', 'LBXSBU', 'LBXSUA', 'avg_systolic',
            'BMXBMI', 'SLD010H'
        ]
    }
}

def train_risk_model(risk_name, config, df_full):
    """Trains a single model and saves the JSON artifact."""
    print(f"\n{'='*60}")
    print(f"Training Model: {risk_name.upper().replace('_', ' ')}")
    print(f"{'='*60}")

    target_col = config['target']
    feature_cols = config['features']

    # Check Features Availability
    available_feats = [f for f in feature_cols if f in df_full.columns]
    missing_feats = list(set(feature_cols) - set(available_feats))
    
    if missing_feats:
        print(f"Warning: The following features are missing in CSV and will be skipped:")
        print(f"{missing_feats}")
    
    print(f"Target: {target_col}")
    print(f"Features ({len(available_feats)}): {available_feats}")

    # Filter Data by dropping rows where target is missing
    df = df_full.dropna(subset=[target_col]).copy()
    
    
    # Robust Imputation by filling missing numeric values with Median
    X = df[available_feats].copy()
    y = df[target_col].copy()

    for col in X.columns:
        if X[col].dtype in ['float64', 'int64']:
            median_val = X[col].median()
            X[col] = X[col].fillna(median_val)
 
    # Three-Way Split 60% Train, 20% Validation, 20% Test
    X_train_full, X_test, y_train_full, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )
    X_train, X_val, y_train, y_val = train_test_split(
        X_train_full, y_train_full, test_size=0.25, random_state=42, stratify=y_train_full
    )

    print(f"Data Split: Train={len(X_train)}, Val={len(X_val)}, Test={len(X_test)}")

    # Handle Class Imbalance by calculating scale_pos_weight
    neg_count = (y_train == 0).sum()
    pos_count = (y_train == 1).sum()
    scale_weight = neg_count / pos_count if pos_count > 0 else 1.0

    # Train XGBoost
    dtrain = xgb.DMatrix(X_train, label=y_train)
    dval = xgb.DMatrix(X_val, label=y_val)
    dtest = xgb.DMatrix(X_test, label=y_test)
    
    params = {
        'objective': 'binary:logistic',
        'eval_metric': 'auc',
        'scale_pos_weight': scale_weight,
        'max_depth': 5,
        'learning_rate': 0.05,
        'n_estimators': 300,
        'early_stopping_rounds': 20,
        'random_state': 42
    }

    model = xgb.train(
        params,
        dtrain,
        num_boost_round=params['n_estimators'],
        evals=[(dtrain, 'Train'), (dval, 'Val')],
        verbose_eval=False,
        early_stopping_rounds=20
    )

    # Final Evaluation
    preds = model.predict(dtest)
    roc = roc_auc_score(y_test, preds)
    print(f"Training Complete. Test Set ROC-AUC: {roc:.4f}")

    # Save Artifacts
    save_path = MODELS_DIR / risk_name
    save_path.mkdir(parents=True, exist_ok=True)
    
    # Save Model
    model_file = save_path / 'model.json'
    model.save_model(str(model_file))
    
    # Save Feature Mapping
    feature_file = save_path / 'features.json'
    with open(feature_file, 'w') as f:
        json.dump(available_feats, f, indent=4)
        
    print(f"Saved artifacts to: {save_path}")

def main():
    if not DATA_PATH.exists():
        raise FileNotFoundError(f"Dataset not found at {DATA_PATH}")
    
    print("Loading dataset...")
    df_full = pd.read_csv(DATA_PATH)
    print(f"Dataset Loaded. Shape: {df_full.shape}")

    # Loop through all risks and train
    for risk_name, config in RISK_CONFIG.items():
        try:
            train_risk_model(risk_name, config, df_full)
        except Exception as e:
            print(f"Failed to train {risk_name}: {str(e)}")

if __name__ == "__main__":
    main()