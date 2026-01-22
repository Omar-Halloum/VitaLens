import { API_BASE_URL } from "../config/api";
import type { AuthResponse, RegisterData } from "../types/auth";

export async function login(
  email: string,
  password: string
): Promise<AuthResponse> {
  try {
    console.log("Login request:", `${API_BASE_URL}/login`); // For testing

    const res = await fetch(`${API_BASE_URL}/login`, {
      method: "POST",
      headers: { 
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({ email, password }),
    });

    console.log("Response:", res.status); // For testing

    if (!res.ok) {
      const error = await res.json().catch(() => ({}));
      console.error("Login error:", error); // For testing

      throw new Error("Unable to sign in, wrong email or password");
    }

    const data = await res.json();
    return data.payload || data;
  } catch (error) {
    console.error("Login failed:", error); // For testing
    if (error instanceof TypeError || (error as Error).message.includes("fetch")) {
      throw new Error("Unable to connect. Please try again later.");
    }
    throw error;
  }
}

export async function register(registerData: RegisterData): Promise<AuthResponse> {
  try {
    console.log("Register request:", `${API_BASE_URL}/register`); // For testing

    const res = await fetch(`${API_BASE_URL}/register`, {
      method: "POST",
      headers: { 
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify(registerData),
    });

    console.log("Response:", res.status); // For testing

    if (!res.ok) {
      const error = await res.json().catch(() => ({}));
      console.error("Register error:", error); // For testing
      
      throw new Error("Unable to create account. Please try again.");
    }

    const data = await res.json();
    return data.payload || data;
  } catch (error) {
    console.error("Registration failed:", error); // For testing
    if (error instanceof TypeError || (error as Error).message.includes("fetch")) {
      throw new Error("Unable to connect. Please try again later.");
    }
    throw error;
  }
}