export interface AuthUser {
  id: number;
  name: string;
  email: string;
  gender: number;
  birth_date: string;
  height: number;
  weight: number;
  created_at: string;
}

export interface AuthResponse extends AuthUser {
  token: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  gender: number;
  birth_date: string;
  height: number;
  weight: number;
}