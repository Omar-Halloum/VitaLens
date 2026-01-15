import styles from './RiskControls.module.css';

export type TimeRange = '1m' | '3m' | '6m' | '1y';

interface RiskControlsProps {
  range: TimeRange;
  onChange: (range: TimeRange) => void;
  riskName: string;
}

export function RiskControls({ range, onChange, riskName }: RiskControlsProps) {
  const ranges: { value: TimeRange; label: string }[] = [
    { value: '1m', label: '1 Month' },
    { value: '3m', label: '3 Months' },
    { value: '6m', label: '6 Months' },
    { value: '1y', label: '1 Year' },
  ];

  return (
    <div className={styles.controlsBar}>
      <div className={styles.timeSelector}>
        {ranges.map((r) => (
          <button
            key={r.value}
            className={`${styles.timeBtn} ${range === r.value ? styles.active : ''}`}
            onClick={() => onChange(r.value)}
          >
            {r.label}
          </button>
        ))}
      </div>
      <div className={styles.dataInfo}>
        <i className="fas fa-info-circle"></i> Viewing: {riskName}
      </div>
    </div>
  );
}