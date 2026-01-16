import { useState } from 'react';
import type { HabitLog } from '../../../types/habitLog';
import styles from './LogList.module.css';

interface LogListProps {
  logs: HabitLog[];
  isLoading: boolean;
}

export function LogList({ logs, isLoading }: LogListProps) {
  const [expandedIds, setExpandedIds] = useState<Set<number>>(new Set([logs[0]?.id]));

  const toggleLog = (id: number) => {
    setExpandedIds(prev => {
      const newSet = new Set(prev);
      if (newSet.has(id)) {
        newSet.delete(id);
      } else {
        newSet.add(id);
      }
      return newSet;
    });
  };

  if (isLoading) {
    return (
      <div className={styles.loading}>
        <i className="fas fa-spinner fa-spin"></i>
        <p>Loading habit logs...</p>
      </div>
    );
  }

  if (logs.length === 0) {
    return (
      <div className={styles.empty}>
        <div className={styles.emptyIcon}>
          <i className="fas fa-clipboard-list"></i>
        </div>
        <h3 className={styles.emptyTitle}>No habit logs yet</h3>
        <p className={styles.emptyText}>Log your first habits to see your history</p>
      </div>
    );
  }

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <div className={styles.logsList}>
      {logs.map((log) => {
        const isExpanded = expandedIds.has(log.id);

        return (
          <div key={log.id} className={styles.logCard}>
            <div className={styles.logHeader} onClick={() => toggleLog(log.id)}>
              <div className={styles.logDate}>{formatDate(log.created_at)}</div>
              <button className={styles.toggleBtn}>
                <i className={`fas fa-chevron-down ${isExpanded ? styles.rotated : ''}`}></i>
              </button>
            </div>
            
            <div className={`${styles.logContent} ${isExpanded ? styles.expanded : ''}`}>
              <div className={styles.userText}>"{log.raw_text}"</div>
              
              {log.ai_insight && (
                <div className={styles.insightSection}>
                  <div className={styles.insightHeader}>
                    <i className="fas fa-brain"></i>
                    <span>AI Health Insight</span>
                  </div>
                  <div className={styles.insightText}>
                    {log.ai_insight}
                  </div>
                </div>
              )}
            </div>
          </div>
        );
      })}
    </div>
  );
}