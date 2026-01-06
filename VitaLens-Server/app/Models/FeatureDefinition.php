<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureDefinition extends Model
{
    protected $fillable = ['feature_name', 'display_name'];

    public function engineeredFeatures()
    {
        return $this->hasMany(EngineeredFeature::class);
    }

    public function riskRequirements()
    {
        return $this->hasMany(RiskRequirement::class);
    }
}
