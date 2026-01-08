<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeatureDefinition;

class FeatureDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            // Demographics (From User Table)
            [
                'feature_name' => 'age', 
                'display_name' => 'Age'
            ],
            [
                'feature_name' => 'gender', 
                'display_name' => 'Gender'
            ],

            // Vitals & Body (From Body/Medical Metrics)
            [
                'feature_name' => 'bmi', 
                'display_name' => 'Body Mass Index'
            ],
            [
                'feature_name' => 'weight', 
                'display_name' => 'Weight'
            ],
            [
                'feature_name' => 'height', 
                'display_name' => 'Height'
            ],
            [
                'feature_name' => 'systolic_bp', 
                'display_name' => 'Systolic Blood Pressure'
            ],
            [
                'feature_name' => 'diastolic_bp', 
                'display_name' => 'Diastolic Blood Pressure'
            ],
            [
                'feature_name' => 'waist_circumference', 
                'display_name' => 'Waist Circumference'
            ],

            // Labs (From Medical Metrics)
            [
                'feature_name' => 'fasting_glucose', 
                'display_name' => 'Fasting Glucose'
            ],
            [
                'feature_name' => 'hba1c', 
                'display_name' => 'HbA1c (Glycohemoglobin)'
            ],
            [
                'feature_name' => 'ldl_cholesterol', 
                'display_name' => 'LDL Cholesterol'
            ],
            [
                'feature_name' => 'hdl_cholesterol', 
                'display_name' => 'HDL Cholesterol'
            ],
            [
                'feature_name' => 'triglycerides', 
                'display_name' => 'Triglycerides'
            ],
            [
                'feature_name' => 'creatinine', 
                'display_name' => 'Serum Creatinine'
            ],
            [
                'feature_name' => 'bun', 
                'display_name' => 'Blood Urea Nitrogen'
            ],
            [
                'feature_name' => 'uric_acid', 
                'display_name' => 'Uric Acid'
            ],

            // Habits (From Habit Logs)
            [
                'feature_name' => 'smoking_status', 
                'display_name' => 'Smoking Status'
            ],
            [
                'feature_name' => 'alcohol_intake', 
                'display_name' => 'Alcohol Intake'
            ],
            [
                'feature_name' => 'activity_moderate', 
                'display_name' => 'Moderate Activity (mins)'
            ],
            [
                'feature_name' => 'activity_vigorous', 
                'display_name' => 'Vigorous Activity (mins)'
            ],
            [
                'feature_name' => 'sleep_duration', 
                'display_name' => 'Sleep Duration (hours)'
            ],
        ];

        foreach ($features as $feature) {
            FeatureDefinition::firstOrCreate(
                ['feature_name' => $feature['feature_name']],
                ['display_name' => $feature['display_name']]
            );
        }
    }
}