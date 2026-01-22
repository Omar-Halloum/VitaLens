import { useState, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { useHealthData } from '../context/HealthDataContext';
import { RiskCard, RiskCardEmpty } from '../components/RiskCard/RiskCard.tsx';
import { HealthCard } from '../components/HealthCard/HealthCard.tsx';
import { MetricCard, MetricCardEmpty } from '../components/MetricCard/MetricCard.tsx';
import { MetricChart } from '../components/MetricChart/MetricChart.tsx';
import type { ChartDataPoint } from '../components/MetricChart/MetricChart.tsx';
import { useGetRiskHistory } from '../hooks/useGetRiskHistory';
import { useGetFeatureHistory } from '../hooks/useGetFeatureHistory';
import { calculateMetric, aggregateByWeek } from '../utils/chartUtils';
import styles from '../styles/DashboardPage.module.css';

export function DashboardPage() {
  const navigate = useNavigate();
  const { riskPredictions, latestFeatures, isLoading } = useHealthData();
  
  // Chart selector states
  const [selectedHabitMetric, setSelectedHabitMetric] = useState('sleep_duration');
  const [selectedMedicalMetric, setSelectedMedicalMetric] = useState('fasting_glucose');
  
  const { 
    data: habitHistory,
    isFetching: habitLoading
  } = useGetFeatureHistory(selectedHabitMetric);
  
  const {
    data: medicalHistory,
    isFetching: medicalLoading
  } = useGetFeatureHistory(selectedMedicalMetric);
  
  const {
    data: allRiskHistory
  } = useGetRiskHistory('all');
  
  // Prepare chart data with weekly aggregation (last 4 weeks only)
  const prepareChartData = (history: typeof habitHistory): ChartDataPoint[] => {
    if (!history || history.length === 0) return [];
    
    // Aggregate daily data to weeks
    const aggregated = aggregateByWeek(
      history.map(h => ({
        created_at: h.created_at,
        value: h.feature_value
      }))
    );
    
    // Take only the last 4 weeks and convert to chart format
    return aggregated.slice(-4).map((point, index) => ({
      label: `Week ${index + 1}`,
      value: point.value
    }));
  };
  
  const habitChartData = prepareChartData(habitHistory);
  const medicalChartData = prepareChartData(medicalHistory);

  const getPrimaryColor = () => {
    if (typeof window === 'undefined') return '#2ed3c6';
    const style = getComputedStyle(document.documentElement);
    return style.getPropertyValue('--color-primary').trim() || '#2ed3c6';
  };
  
  const primaryColor = getPrimaryColor();
  
  // Calculate Metric for each risk
  const getRiskMetric = (riskKey: string) => {
    if (!allRiskHistory) return 'stable';

    const specificHistory = allRiskHistory.filter(r => r.risk_type.key === riskKey);
    return calculateMetric(specificHistory);
  };

  // Get priority metrics (with changes)
  const priorityMetrics = useMemo(() => {
    if (!latestFeatures) return [];
    
    const priorityKeys = [
      { key: 'sleep_duration', label: 'Sleep Duration', unit: 'hrs', icon: 'fas fa-bed' },
      { key: 'bmi', label: 'Body Mass Index', unit: '', icon: 'fas fa-weight-scale' },
      { key: 'fasting_glucose', label: 'Fasting Glucose', unit: 'mg/dL', icon: 'fas fa-droplet' },
      { key: 'systolic_bp', label: 'Systolic Blood Pressure', unit: 'mmHg', icon: 'fas fa-heart-pulse' }
    ];

    return priorityKeys.map(pk => {
      const feature = latestFeatures.find(f => f.feature_definition.feature_name === pk.key);
      if (!feature) return null;
      
      // Calculate change (mock for now, ideally compare with previous week from history)
      const change = 0; 
      
      return {
        name: pk.label,
        value: feature.feature_value,
        unit: pk.unit,
        change,
        icon: pk.icon
      };
    }).filter((f): f is NonNullable<typeof f> => f != null);
  }, [latestFeatures]);

  // Chart Options
  const habitMetrics = [
    { value: 'sleep_duration', label: 'Sleep Duration' },
    { value: 'activity_moderate', label: 'Moderate Activity' },
    { value: 'activity_vigorous', label: 'Vigorous Activity' },
    { value: 'alcohol_intake', label: 'Alcohol Intake' },
  ];

  const medicalMetrics = [
    { value: 'fasting_glucose', label: 'Fasting Glucose' },
    { value: 'hba1c', label: 'HbA1c' },
    { value: 'systolic_bp', label: 'Systolic BP' },
    { value: 'diastolic_bp', label: 'Diastolic BP' },
    { value: 'ldl_cholesterol', label: 'LDL Cholesterol' },
  ];

  return (
    <div className={styles.container}>
      <h1 className={styles.pageTitle}>Health Dashboard</h1>

      {/* Risk Predictions*/}
      <section className={styles.section}>
        <div className={styles.sectionHeader}>
          <h2>Your Health Risks</h2>
        </div>
        <div className={styles.riskGrid}>
          {isLoading ? (
            Array(4).fill(0).map((_, i) => (
              <div key={i} className={`${styles.skeleton} ${styles.skeletonCard}`} />
            ))
          ) : riskPredictions && riskPredictions.length > 0 ? (
            riskPredictions.map(risk => (
              <RiskCard 
                key={risk.id} 
                risk={risk} 
                metric={getRiskMetric(risk.risk_type.key)}
                onClick={() => navigate(`/risks/${risk.risk_type.key}`)}
              />
            ))
          ) : (
            <RiskCardEmpty />
          )}
        </div>
      </section>

      {/* Key Metrics Grid (What Changed) */}
      <section className={styles.section}>
        <div className={styles.sectionHeader}>
          <h2>What Changed This Week</h2>
        </div>
        <div className={styles.metricsGrid}>
          {isLoading ? (
             Array(4).fill(0).map((_, i) => (
              <div key={i} className={`${styles.skeleton} ${styles.skeletonMetric}`} />
            ))
          ) : priorityMetrics.length > 0 ? (
            priorityMetrics.map((metric, idx) => (
              <MetricCard
                key={idx}
                name={metric.name}
                value={metric.value}
                unit={metric.unit}
                change={metric.change}
                icon={metric.icon}
              />
            ))
          ) : (
            <MetricCardEmpty />
          )}
        </div>
      </section>

      {/* Health Metrics Charts */}
      <section className={styles.section}>
        <div className={styles.sectionHeader}>
          <h2>Health Metrics (Last 4 Weeks)</h2>
        </div>
        <div className={styles.chartsGrid}>
          {/* Lifestyle Chart */}
          <div className={styles.chartWrapper}>
            <MetricChart
              title="Lifestyle & Habits"
              data={habitChartData}
              type="bar"
              color={primaryColor}
              showSelector
              options={habitMetrics}
              selectedOption={selectedHabitMetric}
              onOptionChange={setSelectedHabitMetric}
              isLoading={isLoading || habitLoading}
            />
          </div>

          {/* Medical Chart */}
          <div className={styles.chartWrapper}>
            <MetricChart
              title="Medical Metrics"
              data={medicalChartData}
              type="line"
              color={primaryColor}
              showSelector
              options={medicalMetrics}
              selectedOption={selectedMedicalMetric}
              onOptionChange={setSelectedMedicalMetric}
              isLoading={isLoading || medicalLoading}
            />
          </div>
        </div>
      </section>
    </div>
  );
}