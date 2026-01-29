import { API_BASE_URL } from "../config/api";
import { getAuthHeader } from "./authHeader";
import type { Chat, SendMessageRequest, SendMessageResponse } from "../types/chat";

export async function fetchChat(): Promise<Chat> {
  const res = await fetch(`${API_BASE_URL}/v1/chat`, {
    headers: getAuthHeader(),
  });
  
  const json = await res.json();
  
  if (!res.ok) {
    throw new Error(json.status || "Failed to fetch chat");
  }
  
  return json.payload as Chat;
}

export async function sendMessage(request: SendMessageRequest): Promise<SendMessageResponse> {
  const res = await fetch(`${API_BASE_URL}/v1/chat/messages`, {
    method: 'POST',
    headers: {
      ...getAuthHeader(),
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(request),
  });
  
  const json = await res.json();
  
  if (!res.ok) {
    throw new Error(json.status || "Failed to send message");
  }
  
  return json.payload as SendMessageResponse;
}