import { useMemo } from 'react';
import type { RiskPrediction } from '../../../types/riskPredictions';
import styles from './RiskSummaryCard.module.css';

interface RiskSummaryCardProps {
  currentRisk: RiskPrediction;
  history?: RiskPrediction[];
}

export function RiskSummaryCard({ currentRisk, history }: RiskSummaryCardProps) {
  const score = (currentRisk.probability * 100).toFixed(0);
  
  // Calculate trend from history if available
  const trend = useMemo(() => {
    if (!history || history.length < 2) return null;
    
    const sorted = [...history].sort((a, b) => 
      new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    );
    
    // Compare latest with earliest in range for "vs start of period"
    const latest = sorted[0].probability;
    const earliest = sorted[sorted.length - 1].probability;
    const diff = latest - earliest;
    
    if (Math.abs(diff) < 0.01) return { dir: 'stable', val: '0%' };
    
    const percentage = (Math.abs(diff) * 100).toFixed(1) + '%';
    return {
      dir: diff > 0 ? 'up' : 'down',
      val: (diff > 0 ? '+' : '') + percentage,
    };
  }, [history]);

  return (
    <div className={styles.card}>
      <div className={styles.header}>
        <div className={styles.title}>{currentRisk.risk_type.display_name} Risk Score</div>
        <span className={styles.badge}>Analysis Ready</span>
      </div>
      
      <div className={styles.valueRow}>
        <div className={styles.scoreValue}>{score}%</div>
        {trend && (
          <div>
            <div className={`${styles.trendBox} ${styles[trend.dir]}`}>
              <i className={`fas fa-arrow-trend-${trend.dir === 'down' ? 'down' : 'up'}`}></i>
              {trend.val}
            </div>
            <div className={styles.trendLabel}>vs start of period</div>
          </div>
        )}
      </div>
      
      <div className={styles.footer}>
        Confidence: <strong>{currentRisk.confidence_level}</strong> · Last updated: Today
      </div>
    </div>
  );
}