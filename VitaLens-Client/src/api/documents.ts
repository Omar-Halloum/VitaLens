import { API_BASE_URL } from "../config/api";
import { getAuthHeader } from "./authHeader";
import type { Document, DocumentRaw } from "../types/document";

export async function fetchDocuments(): Promise<Document[]> {
  const res = await fetch(`${API_BASE_URL}/v1/get-documents`, {
    headers: getAuthHeader(),
  });
  
  const json = await res.json();
  
  if (!res.ok) {
    throw new Error(json.payload || "Failed to fetch documents");
  }
  
  // Transform backend response to frontend type with status
  return (json.payload as DocumentRaw[]).map((doc) => ({
    ...doc,
    status: 'parsed' as const,
  }));
}

export async function uploadDocument(file: File): Promise<Document> {
  const formData = new FormData();
  formData.append('document', file);
  
  const res = await fetch(`${API_BASE_URL}/v1/upload-documents`, {
    method: 'POST',
    headers: getAuthHeader(true),
    body: formData,
  });
  
  const json = await res.json();
  
  if (!res.ok) {
    throw new Error(json.status || json.payload || "Failed to upload document");
  }
  
  const doc = json.payload as DocumentRaw;
  
  return {
    ...doc,
    status: 'parsed',
  };
}