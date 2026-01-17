from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
from typing import Optional, Dict
from app.services.RagService import get_rag_service, RagService

router = APIRouter()

class IngestRequest(BaseModel):
    user_id: int
    source_type: str
    source_id: int
    text: str
    date: Optional[str] = None
    
class QueryRequest(BaseModel):
    user_id: int
    query: str
    n_results: Optional[int] = 5

@router.post("/ingest")
def ingest_text(
    request: IngestRequest, 
    service: RagService = Depends(get_rag_service)
):
    try:
        metadata = {}
        if request.date:
            metadata['date'] = request.date
        
        result = service.ingest_text(
            user_id=request.user_id,
            source_type=request.source_type,
            source_id=request.source_id,
            text=request.text,
            metadata=metadata
        )
        
        if not result.get('success'):
            raise HTTPException(status_code=400, detail=result.get('message'))
        
        return result
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/query")
def query_context(
    request: QueryRequest, 
    service: RagService = Depends(get_rag_service)
):
    try:
        result = service.query(
            user_id=request.user_id,
            query_text=request.query,
            n_results=request.n_results
        )
        
        if not result.get('success'):
            raise HTTPException(status_code=400, detail=result.get('message'))
        
        return result
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))