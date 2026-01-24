import { useGetDocuments } from '../hooks/useGetDocuments';
import { DocumentUpload } from '../components/Documents/DocumentUpload/DocumentUpload';
import { DocumentList } from '../components/Documents/DocumentList/DocumentList';
import styles from '../styles/DocumentsPage.module.css';

export function DocumentsPage() {
  const { data: documents = [], isLoading, refetch } = useGetDocuments();

  const handleUploadSuccess = () => {
    refetch();
  };

  return (
    <div className={styles.container}>
      <h1 className={styles.pageTitle}>Medical Documents</h1>

      <section className={styles.section}>
        <DocumentUpload onUploadSuccess={handleUploadSuccess} documentsCount={documents.length} />
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>Your Documents</h2>
        <DocumentList documents={documents} isLoading={isLoading} />
      </section>
    </div>
  );
}