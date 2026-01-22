import { useMemo } from 'react';
import { Line } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip,
} from 'chart.js';
import type { RiskPrediction } from '../../../types/riskPredictions';
import styles from './TrajectoryChart.module.css';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip
);

interface TrajectoryChartProps {
  history?: RiskPrediction[];
  days: number;
  isLoading?: boolean;
}

export function TrajectoryChart({ history, days, isLoading }: TrajectoryChartProps) {
  const chartData = useMemo(() => {
    if (!history || history.length === 0) return { labels: [], values: [] };
    
    // Group by date and keep only the latest prediction per day
    const byDate = history.reduce((acc, prediction) => {
      const date = new Date(prediction.created_at);
      const dateKey = date.toISOString().split('T')[0]; // YYYY-MM-DD
      
      if (!acc[dateKey] || new Date(prediction.created_at) > new Date(acc[dateKey].created_at)) {
        acc[dateKey] = prediction;
      }
      
      return acc;
    }, {} as Record<string, RiskPrediction>);
    
    // Convert back to array and sort
    const deduplicated = Object.values(byDate);
    const sorted = deduplicated.sort((a, b) => 
      new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
    );
    
    // For longer ranges, aggregate by week
    if (days > 60 && sorted.length > 10) {
      const weekly: { label: string; value: number }[] = [];
      let weekNum = 1;
      for (let i = 0; i < sorted.length; i += Math.ceil(sorted.length / 8)) {
        const slice = sorted.slice(i, i + Math.ceil(sorted.length / 8));
        if (slice.length > 0) {
          const avg = slice.reduce((sum, p) => sum + p.probability * 100, 0) / slice.length;
          weekly.push({ label: `Week ${weekNum++}`, value: Math.round(avg) });
        }
      }
      return {
        labels: weekly.map(w => w.label),
        values: weekly.map(w => w.value)
      };
    }
    
    // Daily format
    return {
      labels: sorted.map(p => {
        const date = new Date(p.created_at);
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
      }),
      values: sorted.map(p => Math.round(p.probability * 100))
    };
  }, [history, days]);

  const hasData = chartData.labels.length > 0;

  // Calculate Y-axis bounds
  const minVal = hasData ? Math.min(...chartData.values) : 0;
  const maxVal = hasData ? Math.max(...chartData.values) : 100;
  const padding = Math.max(5, Math.round((maxVal - minVal) * 0.2));
  const yMin = Math.max(0, Math.floor((minVal - padding) / 5) * 5);
  const yMax = Math.min(100, Math.ceil((maxVal + padding) / 5) * 5);

  const data = {
    labels: chartData.labels,
    datasets: [
      {
        data: chartData.values,
        borderColor: '#2ed3c6',
        backgroundColor: 'rgba(46, 211, 198, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointRadius: chartData.values.length > 15 ? 0 : 4,
        pointBackgroundColor: '#2ed3c6',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointHoverRadius: 6,
      },
    ],
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: 'rgba(0, 0, 0, 0.85)',
        titleColor: '#fff',
        bodyColor: '#fff',
        padding: 12,
        cornerRadius: 8,
        displayColors: false,
        callbacks: {
          label: (context: { parsed: { y: number | null } }) => 
            context.parsed.y !== null ? `Risk: ${context.parsed.y}%` : ''
        }
      },
    },
    scales: {
      y: {
        display: true,
        min: yMin,
        max: yMax,
        grid: { 
          color: 'rgba(255, 255, 255, 0.08)',
        },
        ticks: { 
          color: '#94a3b8',
          stepSize: Math.max(5, Math.round((yMax - yMin) / 5)),
          callback: (value: number | string) => `${value}%`,
          font: { size: 12 },
          padding: 8
        },
        border: { display: false }
      },
      x: {
        display: true,
        grid: { display: false },
        ticks: { 
          color: '#94a3b8',
          maxRotation: 45,
          minRotation: 0,
          autoSkip: true,
          maxTicksLimit: 8,
          font: { size: 11 },
          padding: 4
        },
        border: { display: false }
      },
    },
  };

  if (isLoading) {
    return (
      <div className={styles.card}>
        <h3 className={styles.title}>Risk Trajectory</h3>
        <div className={styles.chartContainer}>
          <div className={styles.loading}>Loading chart...</div>
        </div>
      </div>
    );
  }

  if (!hasData) {
    return (
      <div className={styles.card}>
        <h3 className={styles.title}>Risk Trajectory</h3>
        <div className={styles.chartContainer}>
          <div className={styles.empty}>No history data available</div>
        </div>
      </div>
    );
  }

  return (
    <div className={styles.card}>
      <h3 className={styles.title}>Risk Trajectory</h3>
      <p className={styles.caption}>Your predicted risk score over the selected timeframe</p>
      <div className={styles.chartContainer}>
        <Line data={data} options={options} />
      </div>
    </div>
  );
}