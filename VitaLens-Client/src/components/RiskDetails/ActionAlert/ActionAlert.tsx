import styles from './ActionAlert.module.css';

interface ActionAlertProps {
  title: string;
  message: string;
  actionText?: string;
  onAction?: () => void;
}

export function ActionAlert({ title, message, actionText, onAction }: ActionAlertProps) {
  return (
    <div className={styles.alert}>
      <div className={styles.icon}>
        <i className="fas fa-triangle-exclamation"></i>
      </div>
      <div className={styles.content}>
        <div className={styles.title}>{title}</div>
        <div className={styles.text}>{message}</div>
      </div>
      {actionText && onAction && (
        <button className={styles.actionBtn} onClick={onAction}>
          {actionText}
        </button>
      )}
    </div>
  );
}