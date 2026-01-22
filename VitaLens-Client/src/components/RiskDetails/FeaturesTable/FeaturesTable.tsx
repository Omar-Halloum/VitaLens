import styles from './FeaturesTable.module.css';
import type { RiskFactor } from '../../../types/riskFactors';
import { useGetEngineeredFeatures } from '../../../hooks/useGetEngineeredFeatures';
import { getHealthStatus, getStatusColor, type Gender } from '../../../utils/healthThresholds';

interface FeaturesTableProps {
  factors: RiskFactor[];
}

export function FeaturesTable({ factors }: FeaturesTableProps) {
  const { data: latestFeatures } = useGetEngineeredFeatures();

  const genderFeature = latestFeatures?.find(f => f.feature_definition.feature_name === 'gender');
  const userGender = (genderFeature?.feature_value === 2 ? 'Female' : 'Male') as Gender;

  const getValue = (featureName: string) => {
    if (!latestFeatures) return null;
    const found = latestFeatures.find(f => f.feature_definition.feature_name === featureName);
    return found ? found.feature_value : null;
  };

  const formatValue = (featureName: string, val: number | null) => {
    if (val === null) return '-';
    if (featureName === 'gender') return val === 1 ? 'Male' : val === 2 ? 'Female' : 'Other';
    if (featureName === 'age') return `${val} yrs`;
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
            {factors.map((factor) => {
              const val = getValue(factor.feature_name);
              
              const status = val !== null && typeof val === 'number' 
                ? getHealthStatus(factor.feature_name, val, userGender)
                : 'Unknown';
              
              return (
                <tr key={factor.feature_name}>
                  <td className={styles.nameCell}>
                    <div className={styles.name}>{factor.display_name}</div>
                  </td>
                  <td className={styles.valueCell}>
                    {formatValue(factor.feature_name, val)}
                  </td>
                  <td>
                    <span className={`${styles.impactBadge} ${factor.is_required ? styles.high : styles.medium}`}>
                      {factor.is_required ? 'High' : 'Medium'}
                    </span>
                  </td>
                  <td>
                    {status !== 'Unknown' && status !== 'Info' ? (
                        <span 
                            className={styles.statusBadge}
                            style={{ 
                                color: getStatusColor(status),
                                backgroundColor: `${getStatusColor(status)}15`,
                                padding: '4px 8px',
                                borderRadius: '4px',
                                fontWeight: 600,
                                fontSize: '0.85rem'
                            }}
                        >
                            {status}
                        </span>
                    ) : (
                        <span style={{ color: '#9ca3af' }}>-</span>
                    )}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}