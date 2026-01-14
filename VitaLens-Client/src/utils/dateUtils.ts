import { format, subDays, eachDayOfInterval, parseISO } from 'date-fns';

// Format a date string to a human-readable format

export function formatDate(dateStr: string, formatStr: string = "MMM d, yyyy"): string {
  try {
    return format(parseISO(dateStr), formatStr);
  } catch {
    return dateStr;
  }
}


// Get relative time labels for date ranges

export function getDateRangeLabel(days: number): string {
  if (days === 7) return '1W';
  if (days === 30) return '1M';
  if (days === 90) return '3M';
  if (days === 180) return '6M';
  if (days === 365) return '1Y';
  return `${days}d`;
}


// Calculate start date for a given range

export function getStartDate(range: string): Date {
  const daysMap: Record<string, number> = {
    '1W': 7,
    '1M': 30,
    '3M': 90,
    '6M': 180,
    '1Y': 365,
  };
  
  const days = daysMap[range] || 30;
  return subDays(new Date(), days);
}

/**
 * Generate chart labels based on date range
 * For small ranges (1W), use day names
 * For larger ranges, use dates
 */
export function generateChartLabels(startDate: Date, endDate: Date, range: string): string[] {
  const days = eachDayOfInterval({ start: startDate, end: endDate });
  
  if (range === '1W') {
    return days.map((day: Date) => format(day, 'EEE')); // Mon, Tue, Wed
  } else if (range === '1M') {
    return days.filter((_: Date, i: number) => i % 3 === 0).map((day: Date) => format(day, 'MMM d')); // Every 3rd day
  } else {
    return days.filter((_: Date, i: number) => i % 7 === 0).map((day: Date) => format(day, 'MMM d')); // Weekly
  }
}


// Get greeting based on time of day

export function getGreeting(): string {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good Morning';
  if (hour < 18) return 'Good Afternoon';
  return 'Good Evening';
}