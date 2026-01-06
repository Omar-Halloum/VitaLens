<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentText extends Model
{
    protected $fillable = ['document_id', 'extracted_text'];

    public function document()
    {
        return $this->belongsTo(MedicalDocument::class, 'document_id');
    }
}
