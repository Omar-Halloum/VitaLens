<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'Years',            // Age
            'kg',               // Weight (Added from UI context)
            'cm',               // Height, Waist
            'kg/m2',            // BMI
            'mmHg',             // Blood Pressure
            'mg/dL',            // Glucose, Cholesterol
            '%',                // HbA1c
            'Hours',            // Sleep
            'Minutes',          // Activity
            'Number of drinks', // Alcohol
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit]);
        }
    }
}
