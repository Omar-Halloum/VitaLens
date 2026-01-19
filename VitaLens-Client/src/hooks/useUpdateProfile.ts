import { useMutation, useQueryClient } from "@tanstack/react-query";
import { updateUserProfile } from "../api/user";
import type { AuthUser, UpdateProfileData } from "../types/auth";

export const useUpdateProfile = () => {
  const queryClient = useQueryClient();

  return useMutation<AuthUser, Error, UpdateProfileData>({
    mutationFn: updateUserProfile,
    onSuccess: () => {
      // Invalidate user profile data
      queryClient.invalidateQueries({ queryKey: ['userProfile'] });
      
      // Invalidate health data to refresh Dashboard if metrics changed
      queryClient.invalidateQueries({ queryKey: ['riskPredictions'] });
      queryClient.invalidateQueries({ queryKey: ['engineeredFeatures'] });
      queryClient.invalidateQueries({ queryKey: ['riskHistory'] });
      queryClient.invalidateQueries({ queryKey: ['featureHistory'] });
    },
  });
};