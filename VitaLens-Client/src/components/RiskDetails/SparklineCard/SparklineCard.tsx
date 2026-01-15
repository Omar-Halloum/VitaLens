import { useMemo } from 'react';
import { Line } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
} from 'chart.js';
import { useGetFeatureHistory } from '../../../hooks/useGetFeatureHistory';
import type { RiskFactor } from '../../../types/riskFactors';
import styles from './SparklineCard.module.css';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement
);

interface SparklineCardProps {
  factor: RiskFactor;
  currentValue?: number;
  unit?: string;
  days: number;
}

export function SparklineCard({ factor, currentValue, unit = '', days }: SparklineCardProps) {
  const { data: history, isFetching } = useGetFeatureHistory(factor.feature_name, days);
  
  // Prepare sparkline data - sorted by date ascending
  const chartData = useMemo(() => {
    if (!history || history.length === 0) return { values: [], dates: [] };
    
    const sorted = [...history].sort((a, b) => 
      new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
    );
    
    const recent = sorted.slice(-15);
    
    return {
      values: recent.map(h => h.feature_value),
      dates: recent.map(h => {
        const d = new Date(h.created_at);
        return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
      })
    };
  }, [history]);
  
  const hasData = chartData.values.length > 0;

  // Calculate statistics
  const minVal = hasData ? Math.min(...chartData.values) : 0;
  const maxVal = hasData ? Math.max(...chartData.values) : 1;
  const range = maxVal - minVal;
  const yMin = range > 0 ? minVal - range * 0.15 : minVal - 1;
  const yMax = range > 0 ? maxVal + range * 0.15 : maxVal + 1;
  
  // Calculate trend (compare first half average to second half average)
  const trend = useMemo(() => {
    if (chartData.values.length < 4) return 'stable';
    const mid = Math.floor(chartData.values.length / 2);
    const firstHalf = chartData.values.slice(0, mid);
    const secondHalf = chartData.values.slice(mid);
    const firstAvg = firstHalf.reduce((a, b) => a + b, 0) / firstHalf.length;
    const secondAvg = secondHalf.reduce((a, b) => a + b, 0) / secondHalf.length;
    const change = ((secondAvg - firstAvg) / firstAvg) * 100;
    if (change > 3) return 'up';
    if (change < -3) return 'down';
    return 'stable';
  }, [chartData.values]);

  const lineColor = trend === 'down' ? '#22c55e' : trend === 'up' ? '#ef4444' : '#f59e0b';

  const data = {
    labels: chartData.dates,
    datasets: [
      {
        data: chartData.values,
        borderColor: lineColor,
        backgroundColor: `${lineColor}20`,
        borderWidth: 2.5,
        fill: true,
        tension: 0.4,
        pointRadius: 0,
        pointHoverRadius: 4,
        pointHoverBackgroundColor: lineColor,
      },
    ],
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { 
        enabled: true,
        backgroundColor: 'rgba(0, 0, 0, 0.85)',
        titleColor: '#fff',
        bodyColor: '#fff',
        padding: 8,
        cornerRadius: 6,
        displayColors: false,
        callbacks: {
          title: (items: { dataIndex: number }[]) => chartData.dates[items[0]?.dataIndex] || '',
          label: (item: { parsed: { y: number | null } }) => 
            item.parsed.y !== null ? `${item.parsed.y.toFixed(1)} ${unit}`.trim() : ''
        }
      },
    },
    scales: {
      y: {
        display: false,
        min: yMin,
        max: yMax,
      },
      x: {
        display: false,
      },
    },
    interaction: {
      intersect: false,
      mode: 'index' as const,
    },
  };

  // Format display values
  const formatValue = (val: number) => {
    if (val >= 100) return Math.round(val).toString();
    if (val >= 10) return val.toFixed(1);
    return val.toFixed(2);
  };

  return (
    <div className={styles.card}>
      <div className={styles.header}>
        <div>
          <div className={styles.name}>{factor.display_name}</div>
          <div className={styles.value}>
            {currentValue !== undefined ? formatValue(currentValue) : '-'} 
            <span className={styles.unit}>{unit}</span>
          </div>
        </div>
        <span className={`${styles.impactBadge} ${factor.is_required ? styles.high : styles.medium}`}>
          {factor.is_required ? 'High Impact' : 'Medium'}
        </span>
      </div>
      
      <div className={styles.chartWrapper}>
        {isFetching ? (
          <div className={styles.loading}>Loading...</div>
        ) : hasData ? (
          <Line data={data} options={options} />
        ) : (
          <div className={styles.empty}>No trend data</div>
        )}
      </div>
      
      {hasData && (
        <div className={styles.footer}>
          <span className={styles.range}>
            Min: {formatValue(minVal)} — Max: {formatValue(maxVal)}
          </span>
          <span className={`${styles.trend} ${styles[trend]}`}>
            {trend === 'up' && '↑ Increasing'}
            {trend === 'down' && '↓ Decreasing'}
            {trend === 'stable' && '→ Stable'}
          </span>
        </div>
      )}
    </div>
  );
}