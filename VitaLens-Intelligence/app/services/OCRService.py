import fitz
import easyocr
from PIL import Image
from fastapi import UploadFile
import io

class OCRService:
    def __init__(self):
        # Initialize EasyOCR once (loads model into memory)
        # It will download the 'en' model the first time it runs
        self.reader = easyocr.Reader(['en']) 

    async def process_document(self, file: UploadFile, file_type: str) -> str:
        content = await file.read()
        
        # 1. Handle PDF
        if "pdf" in file_type.lower() or file.content_type == "application/pdf":
            return self._process_pdf(content)
            
        # 2. Handle Image
        elif "image" in file_type.lower() or file.content_type.startswith("image/"):
            return self._process_image(content)
            
        else:
            raise ValueError("Unsupported file type. Please upload PDF, JPG, or PNG.")

    def _process_pdf(self, file_content: bytes) -> str:
        """
        1. Try PyMuPDF (fast, digital text)
        2. If empty, convert pages to images and use EasyOCR
        """
        doc = fitz.open(stream=file_content, filetype="pdf")
        full_text = ""
        is_scanned = True

        # Fast Text Extraction (Digital PDF)
        for page in doc:
            text = page.get_text()
            if text.strip():
                full_text += text + "\n"
                is_scanned = False
        
        # If text found, return it
        if not is_scanned and len(full_text.strip()) > 10:
            return full_text.strip()

        # If not, fallback to EasyOCR (Scanned PDF)
        ocr_text = ""
        for page in doc:
            pix = page.get_pixmap()
            img_data = pix.tobytes("png")
            
            page_text_list = self.reader.readtext(img_data, detail=0)
            ocr_text += " ".join(page_text_list) + "\n"
            
        return ocr_text.strip()

    def _process_image(self, file_content: bytes) -> str:

        result_list = self.reader.readtext(file_content, detail=0)
        return " ".join(result_list)
