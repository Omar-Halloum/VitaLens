import { useCallback, useState } from 'react';
import { useUploadDocument } from '../../../hooks/useUploadDocument';
import styles from './DocumentUpload.module.css';

interface DocumentUploadProps {
  onUploadSuccess?: () => void;
  documentsCount?: number;
}

export function DocumentUpload({ onUploadSuccess, documentsCount = 0 }: DocumentUploadProps) {
  const [isDragging, setIsDragging] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [previousDocsCount, setPreviousDocsCount] = useState(0);
  const uploadMutation = useUploadDocument();

  // Check if new document appeared
  const isLoading = isProcessing && documentsCount <= previousDocsCount;

  // Reset processing state when new document appears
  if (isProcessing && documentsCount > previousDocsCount) {
    setIsProcessing(false);
  }

  const validateFile = (file: File): string | null => {
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 10 * 1024 * 1024;

    if (!allowedTypes.includes(file.type)) {
      return 'Invalid file type. Please upload PDF, JPEG, or PNG files.';
    }

    if (file.size > maxSize) {
      return 'File size exceeds 10MB limit.';
    }

    return null;
  };

  const handleFileUpload = useCallback((file: File) => {
    setError(null);
    const validationError = validateFile(file);
    if (validationError) {
      setError(validationError);
      return;
    }

    setPreviousDocsCount(documentsCount);
    setIsProcessing(true);

    uploadMutation.mutate(file, {
      onSuccess: () => {
        setError(null);
        onUploadSuccess?.();
      },
      onError: () => {
        setError('Upload failed. Please check your connection and try again.');
        setIsProcessing(false);
      },
    });
  }, [uploadMutation, onUploadSuccess, documentsCount]);

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);

    const file = e.dataTransfer.files[0];
    if (file) {
      handleFileUpload(file);
    }
  }, [handleFileUpload]);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(true);
  }, []);

  const handleDragLeave = useCallback(() => {
    setIsDragging(false);
  }, []);

  const handleFileInput = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      handleFileUpload(file);
    }
  }, [handleFileUpload]);

  return (
    <div className={styles.container}>
      {error && (
        <div className={styles.errorMessage}>
          <i className="fas fa-exclamation-circle"></i>
          {error}
        </div>
      )}
      
      <div
        className={`${styles.uploadZone} ${isDragging ? styles.dragging : ''} ${isLoading ? styles.uploading : ''} ${error ? styles.errorZone : ''}`}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
      >
        <div className={styles.uploadIcon}>
          <i className={isLoading ? 'fas fa-spinner fa-spin' : 'fas fa-cloud-upload-alt'}></i>
        </div>
        <div className={styles.uploadText}>
          <h3>{isLoading ? 'Uploading & Parsing...' : 'Upload Medical Report'}</h3>
          <p>PDF or image (JPEG, PNG) • Max 10MB</p>
          {!isLoading && (
            <>
              <p className={styles.dragText}>Drag and drop your file here or</p>
              <label htmlFor="file-input" className={styles.browseBtn}>
                Browse Files
              </label>
              <input
                id="file-input"
                type="file"
                accept=".pdf,.jpg,.jpeg,.png"
                onChange={handleFileInput}
                className={styles.fileInput}
              />
            </>
          )}
        </div>
      </div>
    </div>
  );
}