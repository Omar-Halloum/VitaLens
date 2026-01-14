// Get risk alert severity based on probability

export function getRiskSeverity(probability: number): 'low' | 'medium' | 'high' {
  if (probability < 30) return 'low';
  if (probability < 60) return 'medium';
  return 'high';
}


// Get color class for risk level

export function getRiskColor(probability: number): string {
  const severity = getRiskSeverity(probability);
  if (severity === 'low') return 'green';
  if (severity === 'medium') return 'yellow';
  return 'red';
}


 // Get trend arrow icon

export function getTrendIcon(trend: 'up' | 'down' | 'stable'): string {
  if (trend === 'up') return '↑';
  if (trend === 'down') return '↓';
  return '→';
}


// Get trend color (up is bad for risks, down is good)

export function getTrendColor(trend: 'up' | 'down' | 'stable'): string {
  if (trend === 'up') return 'red';
  if (trend === 'down') return 'green';
  return 'gray';
}


// Format percentage for display

export function formatPercentage(value: number, decimals: number = 1): string {
  return `${value.toFixed(decimals)}%`;
}

/**
 * Get alert text based on risk key and trend
 * This is a FALLBACK for when AI is unavailable
 * User asked about AI-generated alerts - this provides static fallback
 */
export function getStaticAlertText(
  riskKey: string,
  trend: 'up' | 'down' | 'stable',
  topFactor?: string
): string {
  const templates: Record<string, Record<string, string>> = {
    diabetes_type2: {
      up: topFactor 
        ? `Your diabetes risk increased. Focus on improving ${topFactor}.`
        : 'Your diabetes risk increased. Focus on maintaining healthy glucose levels.',
      down: 'Great progress! Your diabetes risk is decreasing.',
      stable: 'Your diabetes risk is stable. Keep up your current habits.',
    },
    heart_disease: {
      up: topFactor
        ? `Heart disease risk increased. Pay attention to ${topFactor}.`
        : 'Heart disease risk increased. Monitor your cholesterol and blood pressure.',
      down: 'Excellent! Your heart disease risk is improving.',
      stable: 'Your heart disease risk is stable. Continue your healthy lifestyle.',
    },
    chronic_kidney_disease: {
      up: topFactor
        ? `Kidney disease risk increased. Focus on ${topFactor}.`
        : 'Kidney disease risk increased. Stay hydrated and monitor blood pressure.',
      down: 'Your kidney disease risk is decreasing. Keep it up!',
      stable: 'Your kidney disease risk is stable.',
    },
  };
  
  return templates[riskKey]?.[trend] || 'Monitor your health metrics regularly.';
}

/**
 * Placeholder for AI-generated insights
 * User's backend has AIService - this would call an API endpoint
 * that uses the AI to generate personalized text
 */
export function getAIAlertText(
  riskKey: string,
  _userMetrics: Record<string, number>
): Promise<string> {
  // TODO: Implement API call to backend AI service
  // The backend AIService can analyze user's actual data and generate
  // personalized insights like:
  // "Your glucose levels have been trending up over the past 2 weeks.
  //  Consider reducing sugar intake and increasing physical activity."
  
  return Promise.resolve(getStaticAlertText(riskKey, 'stable'));
}