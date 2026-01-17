export interface ChatMessage {
  id: number;
  chat_id: number;
  role: 'user' | 'assistant';
  content: string;
  created_at: string;
}

export interface Chat {
  id: number;
  messages: ChatMessage[];
}

export interface SendMessageRequest {
  message: string;
}

export interface SendMessageResponse {
  success: boolean;
  message: string;
}