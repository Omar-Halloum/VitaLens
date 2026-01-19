import styles from './ProfileFormField.module.css';

interface ProfileFormFieldProps {
  label: string;
  id: string;
  type: 'text' | 'number';
  value: string;
  onChange: (value: string) => void;
  readOnly: boolean;
  min?: string;
  max?: string;
}

export function ProfileFormField({
  label,
  id,
  type,
  value,
  onChange,
  readOnly,
  min,
  max,
}: ProfileFormFieldProps) {
  return (
    <div className={styles.formGroup}>
      <label htmlFor={id}>{label}</label>
      <input
        type={type}
        id={id}
        className={styles.formControl}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        readOnly={readOnly}
        min={min}
        max={max}
      />
    </div>
  );
}