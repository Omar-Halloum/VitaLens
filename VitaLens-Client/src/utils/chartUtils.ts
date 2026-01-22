import type { RiskPrediction } from '../types/riskPredictions';
import type { EngineeredFeature } from '../types/engineeredFeatures';

/**
 * SMART Y-AXIS SCALING
 * Calculates optimal min/max for chart Y-axis to make small changes visible
 * without exaggerating them
 */
export function calculateChartBounds(values: number[]): { min: number; max: number } {
  if (values.length === 0) return { min: 0, max: 100 };
  
  const min = Math.min(...values);
  const max = Math.max(...values);
  const range = max - min;
  
  // If range is very small (< 5%), use a fixed window around the average
  // If range is very small (< 5), use a fixed window around the value(s)
  if (range < 5) {
    const avg = (min + max) / 2;
    // Ensure it have at least +/- 10 padding, or 10% of value
    const padding = Math.max(10, avg * 0.1);
    return {
      min: Math.max(0, Math.floor(avg - padding)),
      max: Math.ceil(avg + padding)
    };
  }
  
  // Otherwise, add 20% padding to the range
  const padding = range * 0.2;
  return {
    min: Math.max(0, min - padding),
    max: Math.min(100, max + padding),
  };
}

/**
 * Aggregate data points for longer time ranges
 * For 6M/1Y views, group by week to reduce view
 */
export function aggregateByWeek(
  data: Array<{ created_at: string; value: number }>
): Array<{ date: string; value: number }> {
  const weeklyData = new Map<string, number[]>();
  
  data.forEach(item => {
    const date = new Date(item.created_at);
    const weekStart = new Date(date.setDate(date.getDate() - date.getDay()));
    const weekKey = weekStart.toISOString().split('T')[0];
    
    if (!weeklyData.has(weekKey)) {
      weeklyData.set(weekKey, []);
    }
    weeklyData.get(weekKey)!.push(item.value);
  });
  
  return Array.from(weeklyData.entries()).map(([date, values]) => ({
    date,
    value: values.reduce((a, b) => a + b, 0) / values.length,
  }));
}

/**
 * Calculate feature direction based on recent history
 */
export function calculateMetric(
  history: RiskPrediction[]
): 'up' | 'down' | 'stable' {
  if (history.length < 2) return 'stable';
  
  // Sort by date descending
  const sorted = [...history].sort((a, b) => 
    new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  );
  
  const latest = sorted[0].probability;
  const previous = sorted[1].probability;
  const change = latest - previous;
  
  // Consider stable if change is less than 2%
  if (Math.abs(change) < 2) return 'stable';
  return change > 0 ? 'up' : 'down';
}

/**
 * Prepare data for Chart.js Line chart
 */
export function prepareChartData(
  data: Array<{ created_at: string; probability: number }>,
  range: string
): { labels: string[]; values: number[]; bounds: { min: number; max: number } } {
  if (data.length === 0) {
    return { labels: [], values: [], bounds: { min: 0, max: 100 } };
  }
  
  // Sort by date ascending
  const sorted = [...data].sort((a, b) => 
    new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
  );
  
  // For long ranges, aggregate by week
  let processedData = sorted;
  if (range === '6M' || range === '1Y') {
    const aggregated = aggregateByWeek(
      sorted.map(d => ({ created_at: d.created_at, value: d.probability }))
    );
    processedData = aggregated.map(d => ({ created_at: d.date, probability: d.value }));
  }
  
  const labels = processedData.map(d => {
    const date = new Date(d.created_at);
    return range === '1W' 
      ? date.toLocaleDateString('en-US', { weekday: 'short' })
      : date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  });
  
  const values = processedData.map(d => d.probability);
  const bounds = calculateChartBounds(values);
  
  return { labels, values, bounds };
}


// Get top N features with the biggest changes

export function getTopChanges(
  features: EngineeredFeature[],
  limit: number = 4
): Array<{ name: string; value: number; change: number; changePercent: number }> {
  // This would need historical data to calculate actual changes
  // For now, return a placeholder structure
  return features.slice(0, limit).map(f => ({
    name: f.feature_definition.display_name,
    value: f.feature_value,
    change: 0, // TODO: Calculate from history
    changePercent: 0,
  }));
}