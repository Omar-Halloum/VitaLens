import type { KeyboardEvent } from 'react';
import styles from './ChatInput.module.css';

interface ChatInputProps {
  value: string;
  onChange: (value: string) => void;
  onSend: () => void;
  isLoading: boolean;
}

export function ChatInput({ value, onChange, onSend, isLoading }: ChatInputProps) {
  const handleKeyPress = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      onSend();
    }
  };

  return (
    <div className={styles.chatInput}>
      <input
        type="text"
        value={value}
        onChange={(e) => onChange(e.target.value)}
        onKeyPress={handleKeyPress}
        placeholder="Type your question..."
        disabled={isLoading}
      />
      <button
        onClick={onSend}
        disabled={!value.trim() || isLoading}
      >
        {isLoading ? (
          <i className="fas fa-spinner fa-spin"></i>
        ) : (
          <i className="fas fa-paper-plane"></i>
        )}
      </button>
    </div>
  );
}