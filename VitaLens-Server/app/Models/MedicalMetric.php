<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalMetric extends Model
{
    protected $fillable = ['user_id', 'source_document_id', 'health_variable_id', 'unit_id', 'value', 'measured_at'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'measured_at' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(MedicalDocument::class, 'source_document_id');
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
