import { useQuery } from "@tanstack/react-query";
import { fetchEngineeredFeatures } from "../api/engineeredFeatures";
import type { EngineeredFeature } from "../types/engineeredFeatures";

export const useGetEngineeredFeatures = () =>
  useQuery<EngineeredFeature[], Error>({
    queryKey: ['engineeredFeatures'],
    queryFn: fetchEngineeredFeatures,
    staleTime: 5 * 60 * 1000,
  });