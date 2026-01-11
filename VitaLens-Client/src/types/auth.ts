export interface AuthUser {
  id: number;
  username: string;
  email: string;
  gender: string;
  birthdate: string;
  height: number;
  weight: number;
  created_at: string;
}

export interface AuthResponse {
  user: AuthUser;
  authorisation: {
    token: string;
    type: string;
  };
}

export interface RegisterData {
  username: string;
  email: string;
  password: string;
  gender: string;
  birthdate: string;
  height: number;
  weight: number;
}