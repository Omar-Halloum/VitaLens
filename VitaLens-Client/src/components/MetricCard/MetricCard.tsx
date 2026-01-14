import styles from './MetricCard.module.css';

interface MetricCardProps {
  name: string;
  value: string | number;
  unit?: string;
  change?: number;
  changeText?: string;
  icon?: string;
}

export function MetricCard({ 
  name, 
  value, 
  unit = '', 
  change = 0, 
  changeText,
  icon = 'fas fa-chart-simple'
}: MetricCardProps) {
  const getChangeClass = () => {
    // For most metrics, increase is bad (weight, BP), decrease is good
    // For some metrics like steps, increase is good
    if (name.toLowerCase().includes('steps') || name.toLowerCase().includes('activity')) {
      return change > 0 ? styles.good : change < 0 ? styles.bad : styles.neutral;
    }
    return change > 0 ? styles.bad : change < 0 ? styles.good : styles.neutral;
  };
  
  const getChangeIcon = () => {
    if (change > 0) return 'fas fa-arrow-up';
    if (change < 0) return 'fas fa-arrow-down';
    return 'fas fa-minus';
  };
  
  return (
    <div className={styles.card}>
      <div className={styles.header}>
        <span>{name}</span>
        <i className={icon} style={{ color: 'var(--primary)' }}></i>
      </div>
      <div className={styles.value}>
        {value}{unit && <span className={styles.unit}>{unit}</span>}
      </div>
      <div className={`${styles.change} ${getChangeClass()}`}>
        <i className={getChangeIcon()}></i>
        <span>{changeText || `${Math.abs(change)} vs last week`}</span>
      </div>
    </div>
  );
}

// Empty state when no metrics are available
export function MetricCardEmpty() {
  return (
    <div className={`${styles.card} ${styles.empty}`}>
      <div className={styles.emptyIcon}>
        <i className="fas fa-clipboard-list"></i>
      </div>
      <div className={styles.emptyTitle}>No Changes Yet</div>
      <div className={styles.emptyText}>
        Log your daily habits to track weekly changes.
      </div>
    </div>
  );
}