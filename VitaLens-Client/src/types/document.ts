export type DocumentStatus = 'parsed' | 'pending' | 'error';

export interface DocumentRaw {
  id: number;
  user_id: number;
  file_path: string;
  file_type: string;
  created_at: string;
  document_date?: string | null;
}

export interface Document extends DocumentRaw {
  status: DocumentStatus;
}