/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useCallback } from 'react';
import type { ReactNode } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { useGetRiskPredictions } from '../hooks/useGetRiskPredictions';
import { useGetEngineeredFeatures } from '../hooks/useGetEngineeredFeatures';
import type { RiskPrediction } from '../types/riskPredictions';
import type { EngineeredFeature } from '../types/engineeredFeatures';

interface HealthDataContextType {
  // Shared Data
  riskPredictions: RiskPrediction[] | undefined;
  latestFeatures: EngineeredFeature[] | undefined;
  
  isLoading: boolean;
  error: Error | null;
  
  // Accessors
  getRiskByKey: (key: string) => RiskPrediction | undefined;
  getFeatureByName: (name: string) => EngineeredFeature | undefined;
  
  // Actions
  refreshHealthData: () => Promise<void>;
}

const HealthDataContext = createContext<HealthDataContextType | undefined>(undefined);

export function HealthDataProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();

  // Fetch Shared Data
  const risksQuery = useGetRiskPredictions();
  const featuresQuery = useGetEngineeredFeatures();

  // Accessors
  const getRiskByKey = useCallback((key: string) => {
    return risksQuery.data?.find(r => r.risk_type.key === key);
  }, [risksQuery.data]);

  const getFeatureByName = useCallback((name: string) => {
    return featuresQuery.data?.find(f => f.feature_definition.feature_name === name);
  }, [featuresQuery.data]);

  // Refresh Action (after upload/login/update)
  const refreshHealthData = useCallback(async () => {
    await Promise.all([
      queryClient.invalidateQueries({ queryKey: ['riskPredictions'] }),
      queryClient.invalidateQueries({ queryKey: ['engineeredFeatures'] }),
    ]);
  }, [queryClient]);

  const value = {
    riskPredictions: risksQuery.data,
    latestFeatures: featuresQuery.data,
    isLoading: risksQuery.isLoading || featuresQuery.isLoading,
    error: (risksQuery.error || featuresQuery.error) as Error | null,
    getRiskByKey,
    getFeatureByName,
    refreshHealthData,
  };

  return (
    <HealthDataContext.Provider value={value}>
      {children}
    </HealthDataContext.Provider>
  );
}

export const useHealthData = () => {
  const context = useContext(HealthDataContext);
  if (!context) {
    throw new Error('useHealthData must be used within a HealthDataProvider');
  }
  return context;
};