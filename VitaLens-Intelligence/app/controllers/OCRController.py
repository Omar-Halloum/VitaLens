from fastapi import UploadFile, File, Form, HTTPException
from app.services.OCRService import OCRService

ocr_service = OCRService()

async def extract_text(
    file: UploadFile = File(...), 
    file_type: str = Form(...)
):
    try:
        extracted_text = await ocr_service.process_document(file, file_type)
        
        return {
            "status": "success",
            "filename": file.filename,
            "extracted_text": extracted_text
        }
    except ValueError as ve:
        raise HTTPException(status_code=400, detail=str(ve))
    except Exception as e:
        print(f"Error: {e}")
        raise HTTPException(status_code=500, detail="Internal Server Error during OCR")