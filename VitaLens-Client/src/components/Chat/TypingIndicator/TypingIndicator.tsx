import styles from './TypingIndicator.module.css';

export function TypingIndicator() {
  return (
    <div className={styles.message}>
      <div className={styles.typingIndicator}>
        <span className={styles.dot}></span>
        <span className={styles.dot}></span>
        <span className={styles.dot}></span>
      </div>
    </div>
  );
}