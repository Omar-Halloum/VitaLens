import { API_BASE_URL } from "../config/api";
import { getAuthHeader } from "./authHeader";
import type {
    RiskPrediction,
    RiskPredictionRaw,
} from "../types/riskPredictions";

export async function fetchRiskPredictions(): Promise<RiskPrediction[]> {
    const res = await fetch(`${API_BASE_URL}/risk-predictions`, {
        headers: getAuthHeader(),
    });

    const json = await res.json();

    if (!res.ok) {
        throw new Error(json.payload || "Failed to fetch risks");
    }

    return (json.payload as RiskPredictionRaw[]).map((item) => ({
        ...item,
        probability: parseFloat(item.probability),
    }));
}

export async function fetchRiskHistory(
    riskKey: string | null = null,
    days: number = 30
): Promise<RiskPrediction[]> {
    const startDate = new Date(
        Date.now() - days * 24 * 60 * 60 * 1000
    ).toISOString();

    // Build URL with optional riskKey
    const endpoint = riskKey ? `/risk-history/${riskKey}` : "/risk-history";

    const url = `${API_BASE_URL}${endpoint}?start_date=${startDate}`;

    const res = await fetch(url, {
        headers: getAuthHeader(),
    });

    const json = await res.json();

    if (!res.ok) {
        throw new Error(json.payload || "Failed to fetch risk history");
    }

    return (json.payload as RiskPredictionRaw[]).map((item) => ({
        ...item,
        probability: parseFloat(item.probability),
    }));
}
