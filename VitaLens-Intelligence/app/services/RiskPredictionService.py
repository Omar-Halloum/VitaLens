import xgboost as xgb
import numpy as np
import json
from pathlib import Path
from typing import Dict, List, Optional

class RiskPredictionService:
    
    # Feature name mapping: Laravel keys → NHANES codes used by models
    def __init__(self):
        self.models_dir = Path(__file__).parent.parent / 'ml_training' / 'models'
        self.config_path = Path(__file__).parent.parent / 'configs' / 'feature_mapping.json'
        
        self.models: Dict[str, xgb.Booster] = {}
        self.model_features: Dict[str, List[str]] = {}
        self.feature_mapping: Dict[str, str] = {}
        
        self._load_feature_mapping()
        self._load_all_models()

    def _load_feature_mapping(self):
        """Load feature mapping from JSON config."""
        if self.config_path.exists():
            with open(self.config_path, 'r') as f:
                self.feature_mapping = json.load(f)
            print("Loaded feature mapping")
        else:
            print(f"Feature mapping not found at {self.config_path}")
            self.feature_mapping = {}
    
    # Required features for each risk, used for confidence calculation
    REQUIRED_FEATURES = {
        'diabetes': ['age', 'gender', 'bmi', 'fasting_glucose', 'hba1c'],
        'heart_disease': ['age', 'gender', 'systolic_bp', 'diastolic_bp', 'ldl_cholesterol'],
        'hypertension': ['age', 'systolic_bp', 'diastolic_bp', 'bmi'],
        'kidney_disease': ['age', 'creatinine', 'bun', 'uric_acid', 'systolic_bp']
    }
    
    def _load_all_models(self):
        # Load all trained models and their feature lists
        risk_types = ['diabetes', 'heart_disease', 'hypertension', 'kidney_disease']
        
        for risk in risk_types:
            model_path = self.models_dir / risk / 'model.json'
            features_path = self.models_dir / risk / 'features.json'
            
            if model_path.exists() and features_path.exists():
                booster = xgb.Booster()
                booster.load_model(str(model_path))
                self.models[risk] = booster
                
                with open(features_path, 'r') as f:
                    self.model_features[risk] = json.load(f)
                    
                print(f"Loaded model: {risk} ({len(self.model_features[risk])} features)")
            else:
                print(f"Model not found for: {risk}")
    
    def translate_features(self, php_features: Dict) -> Dict:
        # Convert PHP feature names to NHANES codes used by models
        nhanes_features = {}
        for php_key, value in php_features.items():
            nhanes_key = self.feature_mapping.get(php_key, php_key)
            nhanes_features[nhanes_key] = value
        return nhanes_features
    
    def calculate_confidence(self, risk_type: str, provided_features: Dict) -> Dict:
        # Calculate confidence based on feature availability
        required = self.REQUIRED_FEATURES.get(risk_type, [])
        model_feature_list = self.model_features.get(risk_type, [])
        
        # Check which required features are missing
        missing_required = []
        for feat in required:
            nhanes_code = self.feature_mapping.get(feat, feat)
            if nhanes_code not in provided_features or provided_features.get(nhanes_code) is None:
                missing_required.append(feat)
        
        # Check all model features
        missing_optional = []
        for nhanes_feat in model_feature_list:
            if nhanes_feat not in provided_features or provided_features.get(nhanes_feat) is None:
                # Find the PHP name for display
                php_name = next((k for k, v in self.feature_mapping.items() if v == nhanes_feat), nhanes_feat)
                if php_name not in required and php_name not in missing_required:
                    missing_optional.append(php_name)
        
        total_required = len(required)
        total_features = len(model_feature_list)
        present_required = total_required - len(missing_required)
        present_total = total_features - len(missing_required) - len(missing_optional)
        
        # Calculate confidence score
        if missing_required:
            # Missing required features = low confidence
            confidence_score = (present_required / total_required) * 0.5 if total_required > 0 else 0.5
            confidence_level = 'low'
        else:
            # All required present, check optionals
            optional_count = total_features - total_required
            if optional_count > 0:
                present_optional = optional_count - len(missing_optional)
                confidence_score = 0.5 + ((present_optional / optional_count) * 0.5)
            else:
                confidence_score = 1.0
            
            if confidence_score >= 0.85:
                confidence_level = 'high'
            elif confidence_score >= 0.65:
                confidence_level = 'medium'
            else:
                confidence_level = 'low'
        
        return {
            'confidence_level': confidence_level,
            'confidence_score': round(confidence_score, 2),
            'features_used': present_total,
            'features_total': total_features,
            'missing_required': missing_required,
            'missing_optional': missing_optional
        }
    
    def predict_single(self, risk_type: str, nhanes_features: Dict) -> Optional[float]:
        # Make a prediction for a single risk type
        if risk_type not in self.models:
            return None
        
        model = self.models[risk_type]
        feature_order = self.model_features[risk_type]
        
        # Build feature array in the correct order
        feature_values = []
        for feat_name in feature_order:
            value = nhanes_features.get(feat_name)
            if value is None:
                value = np.nan
            feature_values.append(float(value) if value is not None else np.nan)
        
        # Create DMatrix and predict
        dmatrix = xgb.DMatrix([feature_values], feature_names=feature_order)
        probability = float(model.predict(dmatrix)[0])
        
        return probability
    
    def predict_all_risks(self, php_features: Dict) -> List[Dict]:
        # Translate PHP names to NHANES codes
        nhanes_features = self.translate_features(php_features)
        
        predictions = []
        
        for risk_type in self.models.keys():
            # Calculate confidence based on available features
            confidence_info = self.calculate_confidence(risk_type, nhanes_features)
            
            # Skip if ALL required features are missing
            if confidence_info['features_used'] == 0:
                continue
            
            probability = self.predict_single(risk_type, nhanes_features)
            
            if probability is not None:
                predictions.append({
                    'risk_type': risk_type,
                    'probability': round(probability, 4),
                    'confidence_level': confidence_info['confidence_level'],
                    'confidence_score': confidence_info['confidence_score'],
                    'features_used': confidence_info['features_used'],
                    'features_total': confidence_info['features_total'],
                    'missing_required': confidence_info['missing_required'],
                    'missing_optional': confidence_info['missing_optional']
                })
        
        return predictions


# Singleton instance for the service
_service_instance: Optional[RiskPredictionService] = None

def get_risk_prediction_service() -> RiskPredictionService:
    # Get or create the singleton RiskPredictionService instance
    global _service_instance
    if _service_instance is None:
        _service_instance = RiskPredictionService()
    return _service_instance