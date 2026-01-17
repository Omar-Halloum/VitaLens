import styles from './ChatMessage.module.css';

interface ChatMessageProps {
  role: 'user' | 'assistant';
  content: string;
}

export function ChatMessage({ role, content }: ChatMessageProps) {
  return (
    <div
      className={`${styles.message} ${
        role === 'user' ? styles.userMessage : styles.aiMessage
      }`}
    >
      {content}
    </div>
  );
}