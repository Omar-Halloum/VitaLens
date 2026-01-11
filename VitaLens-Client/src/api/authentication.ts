import { API_BASE_URL } from "../config/api";
import type { AuthResponse, RegisterData } from "../types/auth";

export async function login(
  email: string,
  password: string
): Promise<AuthResponse> {
  const res = await fetch(`${API_BASE_URL}/login`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
  });

  if (!res.ok) {
    const error = await res.json().catch(() => ({}));
    throw new Error(error?.message || "Invalid email or password");
  }

  const data = await res.json();
  return data.payload || data;
}

export async function register(registerData: RegisterData): Promise<AuthResponse> {
  const res = await fetch(`${API_BASE_URL}/register`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(registerData),
  });

  if (!res.ok) {
    const error = await res.json().catch(() => ({}));
    throw new Error(error?.message || "Registration failed");
  }

  const data = await res.json();
  return data.payload || data;
}