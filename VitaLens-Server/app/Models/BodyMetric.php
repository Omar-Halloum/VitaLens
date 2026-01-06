<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BodyMetric extends Model
{
    protected $fillable = ['user_id', 'health_variable_id', 'unit_id', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function healthVariable()
    {
        return $this->belongsTo(HealthVariable::class, 'health_variable_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
