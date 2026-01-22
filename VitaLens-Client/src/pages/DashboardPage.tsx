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
import { calculateMetric, aggregateByWeek, getTopChanges } from '../utils/chartUtils';
import styles from '../styles/DashboardPage.module.css';


const PRIORITY_KEYS = ['sleep_duration', 'bmi', 'fasting_glucose', 'systolic_bp'];

export function DashboardPage() {
  const navigate = useNavigate();
  const { riskPredictions, latestFeatures, isLoading } = useHealthData();
  
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
  
  const { data: allRiskHistory } = useGetRiskHistory('all');
  
  const { data: sleepHistory } = useGetFeatureHistory('sleep_duration', 14);
  const { data: bmiHistory } = useGetFeatureHistory('bmi', 14);
  const { data: glucoseHistory } = useGetFeatureHistory('fasting_glucose', 14);
  const { data: bpHistory } = useGetFeatureHistory('systolic_bp', 14);
  
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

  const priorityMetrics = useMemo(() => {
    if (!latestFeatures) return [];
    
    const featureHistory = new Map<string, Array<{ created_at: string; feature_value: number }>>();
    if (sleepHistory) featureHistory.set('sleep_duration', sleepHistory);
    if (bmiHistory) featureHistory.set('bmi', bmiHistory);
    if (glucoseHistory) featureHistory.set('fasting_glucose', glucoseHistory);
    if (bpHistory) featureHistory.set('systolic_bp', bpHistory);
    
    const priorityFeatures = latestFeatures.filter(f => 
      PRIORITY_KEYS.includes(f.feature_definition.feature_name)
    );
    
    const changes = getTopChanges(priorityFeatures, featureHistory, 4);
    
    const icons = {
      'Sleep Duration': 'fas fa-bed',
      'Body Mass Index': 'fas fa-weight-scale',
      'Fasting Glucose': 'fas fa-droplet',
      'Systolic Blood Pressure': 'fas fa-heart-pulse'
    };
    
    const units = {
      'Sleep Duration': 'hrs',
      'Body Mass Index': '',
      'Fasting Glucose': 'mg/dL',
      'Systolic Blood Pressure': 'mmHg'
    };
    
    return changes.map(c => ({
      name: c.name,
      value: c.value,
      unit: units[c.name as keyof typeof units] || '',
      change: c.change,
      icon: icons[c.name as keyof typeof icons] || 'fas fa-circle'
    }));
  }, [latestFeatures, sleepHistory, bmiHistory, glucoseHistory, bpHistory]);

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
      
      {/* Risk Predictions */}
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