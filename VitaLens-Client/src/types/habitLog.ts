export interface HabitMetric {
  id: number;
  habit_log_id: number;
  health_variable_id: number;
  unit_id: number;
  value: number;
  created_at: string;
  healthVariable: {
    key: string;
    display_name: string;
  };
  unit: {
    name: string;
  };
}

export interface HabitLogRaw {
  id: number;
  user_id: number;
  raw_text: string;
  ai_insight: string | null;
  created_at: string;
  updated_at: string;
}

export interface HabitLog extends HabitLogRaw {
  habitMetrics?: HabitMetric[];
}