import { API_BASE_URL } from "../config/api";
import { getAuthHeader } from "./authHeader";
import type { HabitLog } from "../types/habitLog";

export async function fetchHabitLogs(): Promise<HabitLog[]> {
  const res = await fetch(`${API_BASE_URL}/v1/habit-logs`, {
    headers: getAuthHeader(),
  });
  
  const json = await res.json();
  
  if (!res.ok) {
    throw new Error(json.payload || "Failed to fetch habit logs");
  }
  
  return json.payload as HabitLog[];
}

export async function createHabitLog(habitText: string): Promise<HabitLog> {
  const res = await fetch(`${API_BASE_URL}/v1/log-habit`, {
    method: 'POST',
    headers: getAuthHeader(),
    body: JSON.stringify({ habit_text: habitText }),
  });
  
  const json = await res.json();
  
  if (!res.ok) {
    throw new Error(json.status || json.payload || "Failed to create habit log");
  }
  
  return json.payload as HabitLog;
}