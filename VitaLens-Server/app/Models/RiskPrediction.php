<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskPrediction extends Model
{
    protected $fillable = ['user_id', 'risk_type_id', 'probability', 'confidence_level', 'ai_insight'];

    protected function casts(): array
    {
        return [
            'probability' => 'decimal:4',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function riskType()
    {
        return $this->belongsTo(RiskType::class);
    }
}
