from fastapi import APIRouter
from app.controllers import OCRController

router = APIRouter()

router.post("/ocr/extract")(OCRController.extract_text)