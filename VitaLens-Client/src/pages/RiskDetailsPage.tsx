import { useState, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useGetRiskDetail } from '../hooks/useGetRiskDetail';
import { useGetRiskHistory } from '../hooks/useGetRiskHistory';
import { useGetRiskFactors } from '../hooks/useGetRiskFactors';
import { useGetEngineeredFeatures } from '../hooks/useGetEngineeredFeatures';
import { RiskSummaryCard } from '../components/RiskDetails/RiskSummaryCard/RiskSummaryCard';
import { RiskControls, type TimeRange } from '../components/RiskDetails/RiskControls/RiskControls';
import { TrajectoryChart } from '../components/RiskDetails/TrajectoryChart/TrajectoryChart';
import { SparklineCard } from '../components/RiskDetails/SparklineCard/SparklineCard';
import { FeaturesTable } from '../components/RiskDetails/FeaturesTable/FeaturesTable';
import { AIInsightCard } from '../components/RiskDetails/AIInsightCard/AIInsightCard';
import { ActionAlert } from '../components/RiskDetails/ActionAlert/ActionAlert';
import styles from '../styles/RiskDetailsPage.module.css';

export function RiskDetailsPage() {
  const { riskKey } = useParams<{ riskKey: string }>();
  const [timeRange, setTimeRange] = useState<TimeRange>('3m');
  
  const daysMap: Record<TimeRange, number> = {
    '1m': 30,
    '3m': 90,
    '6m': 180,
    '1y': 365,
  };

  const { data: risk, isLoading: riskLoading, error } = useGetRiskDetail(riskKey || '');
  const { data: history, isFetching: historyLoading } = useGetRiskHistory(riskKey || '', daysMap[timeRange]);
  const { data: factors } = useGetRiskFactors(riskKey || '');
  const { data: features } = useGetEngineeredFeatures();

  // Filter top 3 contributors (Prioritize required)
  const topContributors = useMemo(() => {
    if (!factors) return [];
    // Sort: Required first
    const sorted = [...factors].sort((a, b) => {
      if (typeof a.is_required !== 'boolean' || typeof b.is_required !== 'boolean') return 0;
      return (Number(b.is_required) - Number(a.is_required));
    });
    return sorted.slice(0, 3);
  }, [factors]);

  const getFeatureValue = (featureName: string) => {
    const f = features?.find(item => item.feature_definition.feature_name === featureName);
    return f ? f.feature_value : undefined;
  };

  if (riskLoading) {
    return <div className={styles.loading}>Loading risk details...</div>;
  }

  if (error || !risk) {
    return (
      <div className={styles.error}>
        <h2>Risk Not Found</h2>
        <Link to="/dashboard">Back to Dashboard</Link>
      </div>
    );
  }

  // Check if we should show the alert
  const showAlert = history && history.length >= 2;

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <Link to="/dashboard" className={styles.backLink}>
           &larr; Back to Dashboard
        </Link>
      </header>

      <main>
        <RiskControls 
          range={timeRange} 
          onChange={setTimeRange} 
          riskName={risk.risk_type.display_name} 
        />
        
        {/*showAlert && (
          <ActionAlert
            title="Sustained Risk Increase"
            message="Risk score has shown an upward trend. Focus on improving key health factors to reduce your risk."
            actionText="Log Habits"
            onAction={() =>
          />
        )*/}
        
        <RiskSummaryCard 
          currentRisk={risk} 
          history={history} 
        />

        <TrajectoryChart 
          history={history} 
          days={daysMap[timeRange]} 
          isLoading={historyLoading}
        />

        <section className={styles.section}>
          <h3 className={styles.sectionTitle}>Top Contributing Factors</h3>
          <div className={styles.contributorsGrid}>
            {topContributors.map(factor => (
              <SparklineCard
                key={factor.feature_name}
                factor={factor}
                currentValue={getFeatureValue(factor.feature_name)}
                unit={factor.feature_name === 'sleep_duration' ? 'hrs' : ''} 
                days={daysMap[timeRange]}
              />
            ))}
          </div>
        </section>

        <section className={styles.section}>
            <FeaturesTable factors={factors || []} />
        </section>

        <section className={styles.section}>
           <AIInsightCard insight={risk.ai_insight} />
        </section>

      </main>
    </div>
  );
}