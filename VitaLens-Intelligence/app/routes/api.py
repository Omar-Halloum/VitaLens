from fastapi import APIRouter
from app.controllers import OCRController
from app.controllers.RagController import router as rag_router

router = APIRouter()

router.post("/ocr/extract")(OCRController.extract_text)

# Mount the RAG router
router.include_router(rag_router, prefix="/rag", tags=["RAG"])