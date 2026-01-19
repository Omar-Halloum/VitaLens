import { useQuery } from "@tanstack/react-query";
import { fetchUserProfile } from "../api/user";
import type { AuthUser } from "../types/auth";

export const useGetUserProfile = () =>
  useQuery<AuthUser, Error>({
    queryKey: ['userProfile'],
    queryFn: fetchUserProfile,
    staleTime: 5 * 60 * 1000,
  });