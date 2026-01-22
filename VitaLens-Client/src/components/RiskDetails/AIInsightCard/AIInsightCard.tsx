import styles from './AIInsightCard.module.css';

interface AIInsightCardProps {
  insight: string | null;
}

export function AIInsightCard({ insight }: AIInsightCardProps) {
  if (!insight) {
      return (
        <div className={styles.card}>
          <div className={styles.iconWrapper}>
            <i className="fas fa-robot"></i>
          </div>
          <div className={styles.content}>
            <h3 className={styles.title}>AI Health Tip</h3>
            <p className={styles.text}>
              Check back later for personalized insights based on your latest data.
            </p>
          </div>
        </div>
      );
  }

  return (
    <div className={styles.card}>
      <div className={styles.iconWrapper}>
        <i className="fas fa-robot"></i>
      </div>
      <div className={styles.content}>
        <h3 className={styles.title}>AI Health Tip</h3>
        <p className={styles.text}>
          {insight}
        </p>
      </div>
    </div>
  );
}