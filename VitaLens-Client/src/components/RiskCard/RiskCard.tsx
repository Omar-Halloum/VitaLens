import type { RiskPrediction } from '../../types/riskPredictions';
import styles from './RiskCard.module.css';

interface RiskCardProps {
  risk: RiskPrediction;
  metric?: 'up' | 'down' | 'stable';
  topFactor?: string;
  onClick?: () => void;
}

export function RiskCard({ risk, metric = 'stable', topFactor, onClick }: RiskCardProps) {
  const probability = Math.round(risk.probability * 100);
  
  const getMetricClass = () => {
    if (metric === 'up') return styles.trendUp;
    if (metric === 'down') return styles.trendDown;
    return styles.trendStable;
  };
  
  const getMetricText = () => {
    if (metric === 'up') return '↑ Increasing';
    if (metric === 'down') return '↓ Decreasing';
    return '→ Stable';
  };
  
  return (
    <div className={styles.card} onClick={onClick}>
      <div className={styles.name}>{risk.risk_type.display_name}</div>
      <div className={styles.probability}>{probability}%</div>
      <div className={styles.meta}>
        <span className={getMetricClass()}>{getMetricText()}</span>
        <span> · Confidence: {risk.confidence_level}</span>
      </div>
      {topFactor && (
        <div className={styles.meta}>Driver: {topFactor}</div>
      )}
      <button className={styles.viewBtn}>View Analysis</button>
    </div>
  );
}

// Empty state component
export function RiskCardEmpty() {
  return (
    <div className={`${styles.card} ${styles.empty}`}>
      <div className={styles.emptyIcon}>
        <i className="fas fa-chart-line"></i>
      </div>
      <div className={styles.emptyTitle}>No Risk Data Yet</div>
      <div className={styles.emptyText}>
        Upload your medical documents or log your health metrics to see personalized risk predictions.
      </div>
    </div>
  );
}

// End of component
