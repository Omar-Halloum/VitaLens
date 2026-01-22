import { Line, Bar } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import { calculateChartBounds } from '../../utils/chartUtils';
import styles from './MetricChart.module.css';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

export interface ChartDataPoint {
  label: string;
  value: number;
}

interface MetricChartProps {
  title: string;
  data: ChartDataPoint[];
  type?: 'line' | 'bar';
  color?: string;
  showSelector?: boolean;
  options?: { value: string; label: string }[];
  selectedOption?: string;
  onOptionChange?: (value: string) => void;
  isLoading?: boolean;
  minimal?: boolean;
}

export function MetricChart({ 
  title, 
  data, 
  type = 'line',
  color = '#3b82f6',
  showSelector = false,
  options = [],
  selectedOption,
  onOptionChange,
  isLoading = false,
  minimal = false
}: MetricChartProps) {
  const hasData = data && data.length > 0;
  
  // Prepare chart data only if we have data
  const labels = hasData ? data.map(d => d.label) : [];
  const values = hasData ? data.map(d => d.value) : [];
  const bounds = hasData ? calculateChartBounds(values) : { min: 0, max: 100 };
  
  const chartData = {
    labels,
    datasets: [
      {
        label: title,
        data: values,
        borderColor: color,
        backgroundColor: type === 'line' 
          ? `${color}15`
          : `${color}80`,
        borderWidth: 2,
        fill: type === 'line',
        tension: 0.3,
        borderRadius: type === 'bar' ? 4 : 0,
        borderSkipped: false,
        maxBarThickness: 40,
        pointRadius: minimal ? 0 : (values.length === 1 ? 5 : 3),
        pointHoverRadius: minimal ? 0 : 5,
      },
    ],
  };
  
  // Get theme colors
  const getTextColor = () => {
    if (typeof window === 'undefined') return '#64748b';
    const style = getComputedStyle(document.documentElement);
    return style.getPropertyValue('--text-secondary').trim() || '#64748b';
  };
  
  const getGridColor = () => {
    if (typeof window === 'undefined') return 'rgba(128, 128, 128, 0.05)';
    const style = getComputedStyle(document.documentElement);
    const borderColor = style.getPropertyValue('--border-color').trim();
    return borderColor ? `${borderColor}40` : 'rgba(128, 128, 128, 0.05)';
  };
  
  const textColor = getTextColor();
  const gridColor = getGridColor();
  
  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        enabled: !minimal,
        mode: 'index' as const,
        intersect: false,
        backgroundColor: 'rgba(0, 0, 0, 0.8)',
        padding: 12,
        cornerRadius: 8,
      },
    },
    scales: {
      y: {
        display: !minimal,
        beginAtZero: type === 'bar',
        min: type === 'bar' ? 0 : bounds.min,
        max: type === 'bar' ? undefined : bounds.max,
        grid: { color: gridColor, display: true },
        ticks: { color: textColor, display: true },
        border: { display: true, color: gridColor }
      },
      x: {
        display: !minimal,
        grid: { display: false },
        ticks: { color: textColor },
      },
    },
  };
  
  return (
    <div className={`${styles.card} ${minimal ? styles.minimal : ''}`}>
      {!minimal && (
        <div className={styles.header}>
          <div className={styles.title}>{title}</div>
          {showSelector && options.length > 0 && (
            <select 
              className={styles.selector}
              value={selectedOption}
              onChange={(e) => onOptionChange?.(e.target.value)}
              disabled={isLoading}
            >
              {options.map(opt => (
                <option key={opt.value} value={opt.value}>{opt.label}</option>
              ))}
            </select>
          )}
        </div>
      )}
      <div className={styles.chartContainer}>
        {isLoading ? (
          <div className={`${styles.empty} ${styles.loading}`}>
             <div className={styles.skeletonChart} />
          </div>
        ) : hasData ? (
          type === 'line' ? (
            <Line data={chartData} options={chartOptions} />
          ) : (
            <Bar data={chartData} options={chartOptions} />
          )
        ) : (
          <div className={styles.empty}>
            <div className={styles.emptyIcon}>
              <i className="fas fa-chart-area"></i>
            </div>
            <div className={styles.emptyTitle}>No Data Available</div>
            <div className={styles.emptyText}>
              Start tracking your health to see trends over time.
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

// Empty state
export function MetricChartEmpty({ title }: { title: string }) {
  return (
    <div className={styles.card}>
      <div className={styles.header}>
        <div className={styles.title}>{title}</div>
      </div>
      <div className={styles.empty}>
        <div className={styles.emptyIcon}>
          <i className="fas fa-chart-area"></i>
        </div>
        <div className={styles.emptyTitle}>No Data Available</div>
        <div className={styles.emptyText}>
          Start tracking your health to see trends over time.
        </div>
      </div>
    </div>
  );
}