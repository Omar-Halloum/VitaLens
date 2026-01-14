import { API_BASE_URL } from "../config/api";
import { getAuthHeader } from "./authHeader";
import type {
    EngineeredFeature,
    EngineeredFeatureRaw,
} from "../types/engineeredFeatures";

export async function fetchEngineeredFeatures(): Promise<EngineeredFeature[]> {
    const res = await fetch(`${API_BASE_URL}/engineered-features`, {
        headers: getAuthHeader(),
    });

    const json = await res.json();

    if (!res.ok) {
        throw new Error(json.payload || "Failed to fetch features");
    }

    return (json.payload as EngineeredFeatureRaw[]).map((item) => ({
        ...item,
        feature_value: parseFloat(item.feature_value),
    }));
}

export async function fetchFeatureHistory(
    featureName: string,
    days: number = 30
): Promise<EngineeredFeature[]> {
    const startDate = new Date(
        Date.now() - days * 24 * 60 * 60 * 1000
    ).toISOString();

    const res = await fetch(
        `${API_BASE_URL}/feature-history/${featureName}?start_date=${startDate}`,
        { headers: getAuthHeader() }
    );

    const json = await res.json();

    if (!res.ok) {
        throw new Error(json.payload || "Failed to fetch feature history");
    }

    return (json.payload as EngineeredFeatureRaw[]).map((item) => ({
        ...item,
        feature_value: parseFloat(item.feature_value),
    }));
}
