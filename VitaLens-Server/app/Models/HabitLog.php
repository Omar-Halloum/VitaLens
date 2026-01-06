<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HabitLog extends Model
{
    protected $fillable = ['user_id', 'raw_text'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function habitMetrics()
    {
        return $this->hasMany(HabitMetric::class);
    }
}
