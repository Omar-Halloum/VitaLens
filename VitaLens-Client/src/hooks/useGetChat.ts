import { useQuery } from "@tanstack/react-query";
import { fetchChat } from "../api/chat";

export function useGetChat() {
  return useQuery({
    queryKey: ["chat"],
    queryFn: fetchChat,
  });
}