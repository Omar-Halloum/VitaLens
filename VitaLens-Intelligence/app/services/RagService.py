import os
import chromadb
from chromadb.config import Settings
from sentence_transformers import SentenceTransformer
from langchain_text_splitters import RecursiveCharacterTextSplitter
from typing import List, Dict, Optional
from functools import lru_cache

# Load the model globally so it only happens once at startup
# This prevents reloading the AI model on every API call.
GLOBAL_EMBEDDING_MODEL = SentenceTransformer(os.getenv('EMBEDDING_MODEL', 'all-MiniLM-L6-v2'))

class RagService:
    def __init__(self):
        persist_directory = os.getenv('CHROMA_PERSIST_DIRECTORY', './chroma_data')
        
        self.client = chromadb.PersistentClient(path=persist_directory)
        
        # Use the pre-loaded global model
        self.embedding_model = GLOBAL_EMBEDDING_MODEL
        
        self.text_splitter = RecursiveCharacterTextSplitter(
            chunk_size=500,
            chunk_overlap=50,
            length_function=len,
        )
        
        self.collection = self.client.get_or_create_collection(name="health_data_v1")
    
    def ingest_text(self, user_id: int, source_type: str, source_id: int, text: str, metadata: Dict = None) -> Dict:
        if not text or not text.strip():
            return {"success": False, "message": "Text is empty"}
        
        chunks = self.text_splitter.split_text(text)
        if not chunks:
            return {"success": False, "message": "No chunks created"}
        
        embeddings = self.embedding_model.encode(chunks).tolist()
        
        # Prepare metadata and ensure user_id is in metadata so we can filter by it later
        base_metadata = metadata or {}
        metadatas = []
        for _ in chunks:
            meta = base_metadata.copy()
            meta.update({
                "source_type": source_type,
                "source_id": str(source_id),
                "user_id": str(user_id)
            })
            metadatas.append(meta)
            
        ids = [f"{user_id}_{source_type}_{source_id}_{i}" for i in range(len(chunks))]
        
        self.collection.add(
            ids=ids,
            embeddings=embeddings,
            documents=chunks,
            metadatas=metadatas
        )
        
        return {"success": True, "count": len(chunks)}
    
    def query(self, user_id: int, query_text: str, n_results: int = 5) -> Dict:
        if not query_text.strip():
            return {"success": False}
        
        # Generate embedding for the query itself
        query_embedding = self.embedding_model.encode(query_text).tolist()
        
        results = self.collection.query(
            query_embeddings=[query_embedding],
            n_results=n_results,
            where={"user_id": str(user_id)} 
        )
        
        return {"success": True, "results": results}

# Dependency injection for FastAPI
@lru_cache()
def get_rag_service():
    return RagService()