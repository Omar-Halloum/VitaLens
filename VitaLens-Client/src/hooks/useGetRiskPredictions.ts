import { useQuery } from "@tanstack/react-query";
import { fetchRiskPredictions } from "../api/riskPredictions";
import type { RiskPrediction } from "../types/riskPredictions";

export const useGetRiskPredictions = () =>
  useQuery<RiskPrediction[], Error>({
    queryKey: ['riskPredictions'],
    queryFn: fetchRiskPredictions,
    staleTime: 5 * 60 * 1000,
  });