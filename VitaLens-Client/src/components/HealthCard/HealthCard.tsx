import styles from './HealthCard.module.css';

interface HealthCardProps {
  title?: string;
  message: string;
  type?: 'warning' | 'info' | 'success';
}

export function HealthCard({ 
  title = "Today's Health Focus", 
  message,
  type = 'warning'
}: HealthCardProps) {
  const getTypeClass = () => {
    if (type === 'info') return styles.info;
    if (type === 'success') return styles.success;
    return styles.warning;
  };
  
  const getIcon = () => {
    if (type === 'info') return 'fas fa-info-circle';
    if (type === 'success') return 'fas fa-check-circle';
    return 'fas fa-triangle-exclamation';
  };
  
  return (
    <div className={`${styles.card} ${getTypeClass()}`}>
      <div className={styles.header}>
        <i className={getIcon()}></i>
        <span>{title}</span>
      </div>
      <p className={styles.message} dangerouslySetInnerHTML={{ __html: message }} />
    </div>
  );
}

// Empty state
export function HealthCardEmpty() {
  return (
    <div className={`${styles.card} ${styles.empty}`}>
      <div className={styles.header}>
        <i className="fas fa-check-circle"></i>
        <span>All Caught Up!</span>
      </div>
      <p className={styles.message}>
        No urgent health insights right now. Keep logging your daily habits for personalized recommendations.
      </p>
    </div>
  );
}
