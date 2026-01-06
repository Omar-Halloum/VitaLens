<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatasetVariable extends Model
{
    protected $fillable = ['dataset_id', 'health_variable_id', 'column_name'];

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function healthVariable()
    {
        return $this->belongsTo(HealthVariable::class);
    }
}
