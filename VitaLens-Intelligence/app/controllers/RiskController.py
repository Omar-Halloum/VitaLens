from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import Dict, List, Optional
from app.services.RiskPredictionService import get_risk_prediction_service

router = APIRouter()


class PredictRequest(BaseModel):
    user_id: int
    features: Dict[str, Optional[float]]


class PredictionResult(BaseModel):
    risk_type: str
    probability: float
    confidence_level: str
    confidence_score: float
    features_used: int
    features_total: int
    missing_required: List[str]
    missing_optional: List[str]


class PredictResponse(BaseModel):
    success: bool
    predictions: List[PredictionResult]
    message: Optional[str] = None


@router.post("/all", response_model=PredictResponse)
async def predict_all_risks(request: PredictRequest):
    try:
        service = get_risk_prediction_service()
        
        if not request.features:
            return PredictResponse(
                success=False,
                predictions=[],
                message="No features provided"
            )
        
        predictions = service.predict_all_risks(request.features)
        
        if not predictions:
            return PredictResponse(
                success=False,
                predictions=[],
                message="Could not make predictions. Check if required features are provided."
            )
        
        return PredictResponse(
            success=True,
            predictions=predictions,
            message=f"Generated {len(predictions)} predictions"
        )
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Prediction failed: {str(e)}")