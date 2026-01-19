import { useMutation, useQueryClient } from "@tanstack/react-query";
import { createHabitLog } from "../api/habitLogs";
import type { HabitLog } from "../types/habitLog";

export const useCreateHabitLog = () => {
  const queryClient = useQueryClient();

  return useMutation<HabitLog, Error, string>({
    mutationFn: createHabitLog,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['habitLogs'] });
      
      // Invalidate health data to refresh Dashboard
      queryClient.invalidateQueries({ queryKey: ['riskPredictions'] });
      queryClient.invalidateQueries({ queryKey: ['engineeredFeatures'] });
      queryClient.invalidateQueries({ queryKey: ['riskHistory'] });
      queryClient.invalidateQueries({ queryKey: ['featureHistory'] });
    },
  });
};