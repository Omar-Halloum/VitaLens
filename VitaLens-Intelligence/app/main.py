from fastapi import FastAPI
from app.routes.api import router as api_router

app = FastAPI(title="VitaLens AI Service")

# Register the routes
app.include_router(api_router)

@app.get("/")
def read_root():
    return {"status": "VitaLens AI Service is running"}