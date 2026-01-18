import { useMutation, useQueryClient } from "@tanstack/react-query";
import { uploadDocument } from "../api/documents";
import type { Document } from "../types/document";

export const useUploadDocument = () => {
  const queryClient = useQueryClient();

  return useMutation<Document, Error, File>({
    mutationFn: uploadDocument,
    onSuccess: () => {
      // Invalidate and refetch documents list
      queryClient.invalidateQueries({ queryKey: ['documents'] });
      
      // Invalidate health data to refresh Dashboard
      queryClient.invalidateQueries({ queryKey: ['riskPredictions'] });
      queryClient.invalidateQueries({ queryKey: ['engineeredFeatures'] });
      queryClient.invalidateQueries({ queryKey: ['riskHistory'] });
      queryClient.invalidateQueries({ queryKey: ['featureHistory'] });
    },
  });
};