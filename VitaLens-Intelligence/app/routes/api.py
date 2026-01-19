from fastapi import APIRouter
from app.controllers.OCRController import router as ocr_router
from app.controllers.RagController import router as rag_router
from app.controllers.RiskController import router as risk_router

router = APIRouter()

# Mount the OCR router
router.include_router(ocr_router, prefix="/ocr", tags=["OCR"])

# Mount the RAG router
router.include_router(rag_router, prefix="/rag", tags=["RAG"])

# Mount the Risk Prediction router
router.include_router(risk_router, prefix="/predict", tags=["Risk Prediction"])