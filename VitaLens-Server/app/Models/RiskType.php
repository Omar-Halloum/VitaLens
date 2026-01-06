<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskType extends Model
{
    protected $fillable = ['key', 'display_name'];

    public function riskRequirements()
    {
        return $this->hasMany(RiskRequirement::class);
    }

    public function riskPredictions()
    {
        return $this->hasMany(RiskPrediction::class);
    }
}
