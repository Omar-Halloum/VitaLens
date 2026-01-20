<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_email',
        'drive_folder_link',
        'drive_folder_id',
    ];

    public function patients()
    {
        return $this->hasMany(User::class, 'clinic_id');
    }

    public function reports()
    {
        return $this->hasMany(GeneratedReport::class);
    }
}