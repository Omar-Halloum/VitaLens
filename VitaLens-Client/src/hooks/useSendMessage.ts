import { useMutation, useQueryClient } from "@tanstack/react-query";
import { sendMessage } from "../api/chat";
import type { SendMessageRequest } from "../types/chat";

export function useSendMessage() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (request: SendMessageRequest) => sendMessage(request),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["chat"] });
    },
  });
}