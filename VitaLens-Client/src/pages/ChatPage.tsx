import { useState, useRef, useEffect, useMemo } from 'react';
import { useGetChat } from '../hooks/useGetChat';
import { useSendMessage } from '../hooks/useSendMessage';
import { ChatMessage } from '../components/Chat/ChatMessage/ChatMessage';
import { ChatInput } from '../components/Chat/ChatInput/ChatInput';
import { TypingIndicator } from '../components/Chat/TypingIndicator/TypingIndicator';
import styles from '../styles/ChatPage.module.css';


export function ChatPage() {
  const [input, setInput] = useState('');
  const messagesEndRef = useRef<HTMLDivElement>(null);
  
  const { data: chat, isLoading } = useGetChat();
  const sendMessageMutation = useSendMessage();

  const messages = useMemo(() => chat?.messages || [], [chat?.messages]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  // Show typing indicator if the last message was from the user
  const showTypingIndicator = useMemo(() => {
    if (messages.length === 0) return false;
    const lastMessage = messages[messages.length - 1];
    return lastMessage.role === 'user';
  }, [messages]);

  const handleSend = async () => {
    const text = input.trim();
    if (!text || sendMessageMutation.isPending) return;

    setInput('');
    
    try {
      await sendMessageMutation.mutateAsync({ message: text });
    } catch (error) {
      console.error('Failed to send message:', error);
    }
  };

  return (
    <div className={styles.container}>
      <div>
        <h1 className={styles.pageTitle}>Ask About Your Health</h1>
        <p className={styles.subtitle}>
          Ask questions about your uploaded medical reports and daily logs
        </p>
      </div>

      <div className={styles.messagesContainer}>
        {isLoading ? (
          <div className={styles.loading}>
            <i className="fas fa-spinner fa-spin"></i> Loading chat...
          </div>
        ) : messages.length === 0 ? (
          <ChatMessage 
            role="assistant" 
            content="Hello! I'm your Health AI assistant. I can answer questions about your uploaded lab reports, medical documents, and health history. What would you like to know?"
          />
        ) : (
          messages.map((msg) => (
            <ChatMessage 
              key={msg.id}
              role={msg.role}
              content={msg.content}
            />
          ))
        )}
        {showTypingIndicator && <TypingIndicator />}
        <div ref={messagesEndRef} />
      </div>

      <ChatInput 
        value={input}
        onChange={setInput}
        onSend={handleSend}
        isLoading={sendMessageMutation.isPending}
      />
    </div>
  );
}