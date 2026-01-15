import { useQuery } from "@tanstack/react-query";
import { fetchRiskHistory } from "../api/riskPredictions";
import type { RiskPrediction } from "../types/riskPredictions";

export const useGetRiskHistory = (riskKey: string | null = null, days: number = 30) =>
  useQuery<RiskPrediction[], Error>({
    queryKey: ['riskHistory', riskKey, days],
    queryFn: () => fetchRiskHistory(riskKey, days),
    staleTime: 5 * 60 * 1000,
  });