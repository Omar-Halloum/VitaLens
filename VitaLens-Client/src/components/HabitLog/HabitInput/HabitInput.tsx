import { useState } from 'react';
import { useCreateHabitLog } from '../../../hooks/useCreateHabitLog';
import styles from './HabitInput.module.css';

interface HabitInputProps {
  onSuccess?: () => void;
}

export function DailyInputField({ onSuccess }: HabitInputProps) {
  const [habitText, setHabitText] = useState('');
  const [error, setError] = useState<string | null>(null);
  const createMutation = useCreateHabitLog();

  const handleSubmit = () => {
    if (!habitText.trim()) {
      setError('Please describe your habits first.');
      return;
    }
    setError(null);

    createMutation.mutate(habitText, {
      onSuccess: () => {
        setHabitText('');
        setError(null);
        onSuccess?.();
      },
      onError: (err) => {
        setError('Failed to log habit: ' + err.message);
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
        disabled={createMutation.isPending}
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
        disabled={createMutation.isPending}
      >
        <i className={createMutation.isPending ? 'fas fa-spinner fa-spin' : 'fas fa-robot'}></i>
        {createMutation.isPending ? 'Analyzing...' : 'Analyze with AI'}
      </button>
    </div>
  );
}