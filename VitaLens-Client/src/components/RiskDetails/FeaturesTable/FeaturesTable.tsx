import styles from './FeaturesTable.module.css';
import type { RiskFactor } from '../../../types/riskFactors';
import { useGetEngineeredFeatures } from '../../../hooks/useGetEngineeredFeatures';

interface FeaturesTableProps {
  factors: RiskFactor[];
}

export function FeaturesTable({ factors }: FeaturesTableProps) {
  // Get latest values for all features to display current state
  const { data: latestFeatures } = useGetEngineeredFeatures();

  // Helper to find value
  const getValue = (featureName: string) => {
    if (!latestFeatures) return '-';
    
    // Find independent feature
    const found = latestFeatures.find(f => f.feature_definition.feature_name === featureName);
    if (!found) return '-';

    const val = found.feature_value;

    // Formatting Logic
    if (featureName === 'gender') {
      if (val === 1) return 'Male';
      if (val === 2) return 'Female';
      return 'Other';
    }
    
    if (featureName === 'age') {
      return `${val} yrs`;
    }

    return val;
  };

  return (
    <div className={styles.card}>
      <h3 className={styles.title}>All Contributing Features</h3>
      <div className={styles.tableWrapper}>
        <table className={styles.table}>
          <thead>
            <tr>
              <th>Feature</th>
              <th>Current Value</th>
              <th>Impact</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {factors.map((factor) => (
              <tr key={factor.feature_name}>
                <td className={styles.nameCell}>
                  <div className={styles.name}>{factor.display_name}</div>
                </td>
                <td className={styles.valueCell}>
                  {getValue(factor.feature_name)}
                </td>
                <td>
                  <span className={`${styles.impactBadge} ${factor.is_required ? styles.high : styles.medium}`}>
                    {factor.is_required ? 'High' : 'Medium'}
                  </span>
                </td>
                <td>
                   <span className={styles.statusNormal}>Normal</span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}