from fastapi import APIRouter
from app.controllers import OCRController
from app.controllers.RagController import router as rag_router
from app.controllers.RiskController import router as risk_router

router = APIRouter()

router.post("/ocr/extract")(OCRController.extract_text)

# Mount the RAG router
router.include_router(rag_router, prefix="/rag", tags=["RAG"])

# Mount the Risk Prediction router
router.include_router(risk_router, prefix="/predict", tags=["Risk Prediction"])