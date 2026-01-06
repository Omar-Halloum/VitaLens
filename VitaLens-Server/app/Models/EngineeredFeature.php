<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EngineeredFeature extends Model
{
    protected $fillable = ['user_id', 'feature_definition_id', 'feature_value'];

    protected function casts(): array
    {
        return [
            'feature_value' => 'decimal:4',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function featureDefinition()
    {
        return $this->belongsTo(FeatureDefinition::class);
    }
}
