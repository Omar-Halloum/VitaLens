<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalDocument extends Model
{
    protected $fillable = [
        'user_id',
        'file_path',
        'file_type',
        'document_date'
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentTexts()
    {
        return $this->hasMany(DocumentText::class, 'document_id');
    }

    public function medicalMetrics()
    {
        return $this->hasMany(MedicalMetric::class, 'source_document_id');
    }
}
