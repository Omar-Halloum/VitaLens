import { useQuery } from '@tanstack/react-query';
import { fetchRiskPrediction } from '../api/riskPredictions';
import type { RiskPrediction } from '../types/riskPredictions';

export const useGetRiskDetail = (riskKey: string) => 
  useQuery<RiskPrediction, Error>({
    queryKey: ['riskPrediction', riskKey],
    queryFn: () => fetchRiskPrediction(riskKey),
    enabled: !!riskKey,
    staleTime: 5 * 60 * 1000,
  });