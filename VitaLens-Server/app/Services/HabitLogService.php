<?php

namespace App\Services;

use App\Models\User;
use App\Models\HabitLog;

class HabitLogService
{
    public function createLog(User $user, string $habitText): HabitLog
    {
        $habitLog = new HabitLog;
        $habitLog->user_id = $user->id;
        $habitLog->raw_text = $habitText;
        
        $habitLog->save();
        return $habitLog;
    }

    public function getUserLogs(User $user)
    {
        return HabitLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLogById(int $logId): ?HabitLog
    {
        return HabitLog::find($logId);
    }

    public function getRecentLogs(User $user, int $limit = 7): array
    {
        $logs = HabitLog::where('user_id', $user->id)
            ->with('habitMetrics.healthVariable')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        $context = [];
        foreach ($logs as $log) {
            $metrics = [];
            foreach ($log->habitMetrics as $metric) {
                $metrics[$metric->healthVariable->key] = $metric->value;
            }
            
            $context[] = [
                'date' => $log->created_at->format('Y-m-d'),
                'metrics' => $metrics,
            ];
        }
        
        return $context;
    }
}