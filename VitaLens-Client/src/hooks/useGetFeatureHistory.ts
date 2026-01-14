import { useQuery } from "@tanstack/react-query";
import { fetchFeatureHistory } from "../api/engineeredFeatures";
import type { EngineeredFeature } from "../types/engineeredFeatures";

export const useGetFeatureHistory = (featureName: string, days: number = 30) =>
  useQuery<EngineeredFeature[], Error>({
    queryKey: ['featureHistory', featureName, days],
    queryFn: () => fetchFeatureHistory(featureName, days),
    enabled: !!featureName, // Only run if featureName is provided
    staleTime: 5 * 60 * 1000,
  });