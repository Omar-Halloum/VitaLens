<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'user_type_id',
        'email',
        'password',
        'gender',
        'birth_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    public function medicalDocuments()
    {
        return $this->hasMany(MedicalDocument::class);
    }

    public function medicalMetrics()
    {
        return $this->hasMany(MedicalMetric::class);
    }

    public function bodyMetrics()
    {
        return $this->hasMany(BodyMetric::class);
    }

    public function habitLogs()
    {
        return $this->hasMany(HabitLog::class);
    }

    public function habitMetrics()
    {
        return $this->hasMany(HabitMetric::class);
    }

    public function engineeredFeatures()
    {
        return $this->hasMany(EngineeredFeature::class);
    }

    public function ragConversations()
    {
        return $this->hasMany(RagConversation::class);
    }

    public function riskPredictions()
    {
        return $this->hasMany(RiskPrediction::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}
