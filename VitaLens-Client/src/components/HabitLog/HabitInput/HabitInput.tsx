import { useState } from 'react';
import { useCreateHabitLog } from '../../../hooks/useCreateHabitLog';
import styles from './HabitInput.module.css';

interface HabitInputProps {
  onSuccess?: () => void;
  logsCount?: number;
}

export function HabitInput({ onSuccess, logsCount = 0 }: HabitInputProps) {
  const [habitText, setHabitText] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [previousLogsCount, setPreviousLogsCount] = useState(0);
  const createMutation = useCreateHabitLog();

  // Check if new log appeared
  const isLoading = isProcessing && logsCount <= previousLogsCount;

  // Reset processing state when new log appears
  if (isProcessing && logsCount > previousLogsCount) {
    setIsProcessing(false);
  }

  const handleSubmit = () => {
    if (!habitText.trim()) {
      setError('Please describe your habits first.');
      return;
    }
    setError(null);
    setPreviousLogsCount(logsCount);
    setIsProcessing(true);

    createMutation.mutate(habitText, {
      onSuccess: () => {
        setHabitText('');
        setError(null);
        onSuccess?.();
      },
      onError: (err) => {
        setError('Failed to log habit: ' + err.message);
        setIsProcessing(false);
      },
    });
  };

  return (
    <div className={styles.card}>
      <h2>
        <i className="fas fa-edit"></i> Log Today's Habits
      </h2>
      <textarea
        value={habitText}
        onChange={(e) => {
          setHabitText(e.target.value);
          if (error) setError(null);
        }}
        placeholder="Describe what you did today"
        disabled={isLoading}
        className={error ? styles.errorInput : ''}
      />
      
      {error && (
        <div className={styles.errorMessage}>
          <i className="fas fa-exclamation-circle"></i>
          {error}
        </div>
      )}

      <button
        onClick={handleSubmit}
        className={styles.submitBtn}
        disabled={isLoading}
      >
        <i className={isLoading ? 'fas fa-spinner fa-spin' : 'fas fa-robot'}></i>
        {isLoading ? 'Analyzing...' : 'Analyze with AI'}
      </button>
    </div>
  );
}