import { useQuery } from "@tanstack/react-query";
import { fetchRiskFactors } from "../api/riskFactors";
import type { RiskFactor } from "../types/riskFactors";

export const useGetRiskFactors = (riskKey: string) =>
  useQuery<RiskFactor[], Error>({
    queryKey: ['riskFactors', riskKey],
    queryFn: () => fetchRiskFactors(riskKey),
    enabled: !!riskKey,
    staleTime: 24 * 60 * 60 * 1000, // 24 hours (definitions rarely change)
  });