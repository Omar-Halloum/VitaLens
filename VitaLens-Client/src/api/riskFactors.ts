import { API_BASE_URL } from "../config/api";
import { getAuthHeader } from "./authHeader";
import type { RiskFactor } from "../types/riskFactors";

export async function fetchRiskFactors(riskKey: string): Promise<RiskFactor[]> {
    const res = await fetch(`${API_BASE_URL}/risk-factors/${riskKey}`, {
        headers: getAuthHeader(),
    });

    const json = await res.json();

    if (!res.ok) {
        throw new Error(json.payload || "Failed to fetch risk factors");
    }

    return json.payload;
}
