import type { Document } from '../../../types/document';
import { DocumentStatusBadge } from '../DocumentStatusBadge/DocumentStatusBadge';
import styles from './DocumentList.module.css';

interface DocumentListProps {
  documents: Document[];
  isLoading: boolean;
}

export function DocumentList({ documents, isLoading }: DocumentListProps) {
  if (isLoading) {
    return (
      <div className={styles.loading}>
        <i className="fas fa-spinner fa-spin"></i>
        <p>Loading documents...</p>
      </div>
    );
  }

  if (documents.length === 0) {
    return (
      <div className={styles.empty}>
        <div className={styles.emptyIcon}>
          <i className="fas fa-file-medical"></i>
        </div>
        <div className={styles.emptyTitle}>No Documents Yet</div>
        <div className={styles.emptyText}>
          Upload your first medical document to get started with AI-powered health insights.
        </div>
      </div>
    );
  }

  const getFileName = (filePath: string) => {
    const fullName = filePath.split('/').pop() || filePath;
    let cleanName = fullName.replace(/_\d{14}(\.[^.]+)$/, '$1');
    
    const nameMatch = cleanName.match(/^[A-Z][a-z]+_[A-Z][a-z]+_(.+)$/);
    if (nameMatch) {
        cleanName = nameMatch[1];
    }
    
    return cleanName;
  };

  const getFileIcon = (fileType: string) => {

    const type = fileType ? fileType.toLowerCase() : '';
    
    if (type.includes('pdf')) return 'fas fa-file-pdf';
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].some(ext => type.includes(ext))) return 'fas fa-file-image';
    return 'fas fa-file';
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  return (
    <div className={styles.tableWrapper}>
      <table className={styles.table}>
        <thead>
          <tr>
            <th>File</th>
            <th>Uploaded</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          {documents.map((doc) => (
            <tr key={doc.id}>
              <td>
                <div className={styles.fileName}>
                  <i className={`${getFileIcon(doc.file_type)} ${styles.fileIcon}`}></i>
                  <span>{getFileName(doc.file_path)}</span>
                </div>
              </td>
              <td className={styles.uploadDate}>
                {formatDate(doc.created_at)}
              </td>
              <td>
                <DocumentStatusBadge status={doc.status} />
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}