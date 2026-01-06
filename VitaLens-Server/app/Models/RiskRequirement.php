<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskRequirement extends Model
{
    protected $fillable = ['risk_type_id', 'feature_definition_id', 'is_required'];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function riskType()
    {
        return $this->belongsTo(RiskType::class);
    }

    public function featureDefinition()
    {
        return $this->belongsTo(FeatureDefinition::class);
    }
}
