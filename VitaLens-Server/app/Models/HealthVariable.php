<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthVariable extends Model
{
    protected $fillable = ['unit_id', 'key', 'display_name', 'is_ml_feature'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function medicalMetrics()
    {
        return $this->hasMany(MedicalMetric::class);
    }

    public function bodyMetrics()
    {
        return $this->hasMany(BodyMetric::class);
    }

    public function habitMetrics()
    {
        return $this->hasMany(HabitMetric::class);
    }

    public function datasetVariables()
    {
        return $this->hasMany(DatasetVariable::class);
    }
}
