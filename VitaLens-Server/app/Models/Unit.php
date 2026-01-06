<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name'];

    public function healthVariables()
    {
        return $this->hasMany(HealthVariable::class);
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
}
