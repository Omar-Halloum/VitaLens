import styles from './AIInsightCard.module.css';

export function AIInsightCard() {
  return (
    <div className={styles.card}>
      <div className={styles.iconWrapper}>
        <i className="fas fa-robot"></i>
      </div>
      <div className={styles.content}>
        <h3 className={styles.title}>AI Health Tip</h3>
        <p className={styles.text}>
          Your risk has increased primarily due to a 3-week trend of reduced sleep (&lt; 6hrs). 
          Clinical data suggests that improving sleep to 7+ hours can improve insulin sensitivity 
          by up to 15% within two weeks. Try setting a "Wind Down" alarm at 10:30 PM tonight.
        </p>
      </div>
    </div>
  );
}