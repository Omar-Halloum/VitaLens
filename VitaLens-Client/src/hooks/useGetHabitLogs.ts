import { useQuery } from "@tanstack/react-query";
import { fetchHabitLogs } from "../api/habitLogs";
import type { HabitLog } from "../types/habitLog";

export const useGetHabitLogs = () =>
  useQuery<HabitLog[], Error>({
    queryKey: ['habitLogs'],
    queryFn: fetchHabitLogs,
    staleTime: 5 * 60 * 1000,
  });