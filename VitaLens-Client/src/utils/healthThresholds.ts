export type HealthStatus = 'Normal' | 'Warning' | 'Critical' | 'Unknown' | 'Info';
export type Gender = 'Male' | 'Female';

interface Threshold {
  min?: number;
  max?: number;
  warningMin?: number;
  warningMax?: number;
  criticalMin?: number;
  criticalMax?: number;
}

const thresholds: Record<string, Threshold> = {
  'bmi': { min: 18.5, max: 24.9, warningMin: 25, warningMax: 29.9, criticalMin: 30 },
  'hba1c': { max: 5.6, warningMin: 5.7, warningMax: 6.4, criticalMin: 6.5 },
  'fasting_glucose': { max: 99, warningMin: 100, warningMax: 125, criticalMin: 126 },
  'systolic_bp': { max: 119, warningMin: 120, warningMax: 139, criticalMin: 140 },
  'diastolic_bp': { max: 79, warningMin: 80, warningMax: 89, criticalMin: 90 },
  'cholesterol_total': { max: 199, warningMin: 200, warningMax: 239, criticalMin: 240 },
  'ldl_cholesterol': { max: 99, warningMin: 100, warningMax: 159, criticalMin: 160 },
  'hdl_cholesterol': { min: 60, warningMin: 40, warningMax: 59, criticalMax: 39 },
  'triglycerides': { max: 149, warningMin: 150, warningMax: 199, criticalMin: 200 },
  'sleep_duration': { min: 7, max: 9, warningMin: 6, warningMax: 6.9, criticalMax: 5.9 },
  'activity_moderate': { min: 30, warningMax: 29, criticalMax: 0 },
  'activity_vigorous': { min: 15, warningMax: 14, criticalMax: 0 },
  'waist_circumference': { max: 94, warningMin: 95, warningMax: 101, criticalMin: 102 }, 
  'uric_acid': { min: 3.5, max: 7.2, warningMin: 7.3, warningMax: 8.0, criticalMin: 8.1 },
  'bun': { min: 6, max: 20, warningMin: 21, warningMax: 25, criticalMin: 26 },
  'creatinine': { min: 0.7, max: 1.3, warningMin: 1.4, warningMax: 2.0, criticalMin: 2.1 },
};

const infoFeatures = ['age', 'gender', 'weight', 'height'];

export function getHealthStatus(featureName: string, value: number, gender: Gender): HealthStatus {
  if (infoFeatures.includes(featureName)) return 'Info';

  // Special Logic
  if (featureName === 'smoking_status') return value === 1 ? 'Critical' : 'Normal';
  if (featureName === 'alcohol_intake') return value > 5 ? 'Warning' : 'Normal';

  let threshold = thresholds[featureName];

  // Dynamic Gender Overrides
  if (gender === 'Female') {
    if (featureName === 'hdl_cholesterol') {
       threshold = { ...threshold, warningMin: 50, criticalMax: 49 }; 
    }
    if (featureName === 'waist_circumference') {
       threshold = { ...threshold, criticalMin: 88, warningMin: 80, warningMax: 87 };
    }
  }

  if (!threshold) return 'Unknown';

  // Critical Checks (Priority)
  if (threshold.criticalMin !== undefined && value >= threshold.criticalMin) return 'Critical';
  if (threshold.criticalMax !== undefined && value <= threshold.criticalMax) return 'Critical';
  
  // Warning Checks
  if (threshold.warningMin !== undefined && value >= threshold.warningMin) {
      if (threshold.warningMax === undefined || value <= threshold.warningMax) return 'Warning';
  }
  if (threshold.warningMax !== undefined && value <= threshold.warningMax) return 'Warning';

  // Normal Range Fallbacks
  if (threshold.max !== undefined && value > threshold.max) return 'Warning';
  if (threshold.min !== undefined && value < threshold.min) return 'Warning';

  return 'Normal';
}

export function getStatusColor(status: HealthStatus): string {
    const statusColors: Record<HealthStatus, string> = {
        'Critical': '#ef4444', 
        'Warning': '#f59e0b',
        'Normal': '#10b981',
        'Info': '#6b7280',
        'Unknown': '#9ca3af',
    };
    return statusColors[status] || '#9ca3af';
}