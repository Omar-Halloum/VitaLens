import { useMutation, useQueryClient } from "@tanstack/react-query";
import { sendMessage } from "../api/chat";
import type { SendMessageRequest } from "../types/chat";
import type { Chat, ChatMessage } from "../types/chat";

export function useSendMessage() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (request: SendMessageRequest) => sendMessage(request),
    onMutate: async (newMessage) => {
      await queryClient.cancelQueries({ queryKey: ["chat"] });

      const previousChat = queryClient.getQueryData<Chat>(["chat"]);

      queryClient.setQueryData<Chat>(["chat"], (old) => {
        if (!old) return old;

        const optimisticMessage: ChatMessage = {
          id: Date.now(),
          chat_id: old.id,
          role: 'user',
          content: newMessage.message,
          created_at: new Date().toISOString(),
        };

        return {
          ...old,
          messages: [...old.messages, optimisticMessage],
        };
      });

      return { previousChat };
    },
    onError: (_err, _newMessage, context) => {
      queryClient.setQueryData(["chat"], context?.previousChat);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["chat"] });
    },
  });
}