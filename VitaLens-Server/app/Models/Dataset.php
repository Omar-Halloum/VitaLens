<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    protected $fillable = ['name', 'description'];

    public function datasetVariables()
    {
        return $this->hasMany(DatasetVariable::class);
    }
}
