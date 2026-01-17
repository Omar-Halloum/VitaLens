from fastapi import APIRouter
from app.controllers import OCRController, RagController

router = APIRouter()

router.post("/ocr/extract")(OCRController.extract_text)

router.post("/rag/ingest")(RagController.ingest)
router.post("/rag/query")(RagController.query)