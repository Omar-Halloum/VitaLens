import { API_BASE_URL } from "../config/api";
import { getAuthHeader } from "./authHeader";
import type { AuthUser, UpdateProfileData } from "../types/auth";

export async function fetchUserProfile(): Promise<AuthUser> {
  const res = await fetch(`${API_BASE_URL}/v1/profile`, {
    headers: getAuthHeader(),
  });
  
  const json = await res.json();
  
  if (!res.ok) {
    throw new Error(json.payload || "Failed to fetch user profile");
  }
  
  return json.payload as AuthUser;
}

export async function updateUserProfile(data: UpdateProfileData): Promise<AuthUser> {
  const res = await fetch(`${API_BASE_URL}/v1/update-profile`, {
    method: 'POST',
    headers: {
      ...getAuthHeader(),
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  });
  
  const json = await res.json();
  
  if (!res.ok) {
    throw new Error(json.payload || "Failed to update profile");
  }
  
  return json.payload as AuthUser;
}