import type { DocumentStatus } from '../../../types/document';
import styles from './DocumentStatusBadge.module.css';

interface DocumentStatusBadgeProps {
  status: DocumentStatus;
}

export function DocumentStatusBadge({ status }: DocumentStatusBadgeProps) {
  const getStatusClass = () => {
    switch (status) {
      case 'parsed':
        return styles.parsed;
      case 'pending':
        return styles.pending;
      case 'error':
        return styles.error;
      default:
        return '';
    }
  };

  const getStatusText = () => {
    switch (status) {
      case 'parsed':
        return 'Parsed';
      case 'pending':
        return 'Pending';
      case 'error':
        return 'Error';
      default:
        return status;
    }
  };

  return (
    <span className={`${styles.badge} ${getStatusClass()}`}>
      {status === 'pending' && <i className="fas fa-spinner fa-spin" style={{ marginRight: '6px' }}></i>}
      {getStatusText()}
    </span>
  );
}