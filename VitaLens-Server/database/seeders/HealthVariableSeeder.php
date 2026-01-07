<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HealthVariable;
use App\Models\Unit;

class HealthVariableSeeder extends Seeder
{
    public function run(): void
    {
        $unit = fn($name) => Unit::where('name', $name)->value('id');

        $variables = [
            // Demographics
            [
                'key' => 'age',
                'display_name' => 'Age',
                'unit_id' => $unit('Years'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'gender',
                'display_name' => 'Gender', // 1=Male, 2=Female
                'unit_id' => null, // Categorical
                'is_ml_feature' => true
            ],

            // Habits
            [
                'key' => 'smoking_status',
                'display_name' => 'Smoker (100+ cigs in life)',
                'unit_id' => null, // Categorical (Yes/No)
                'is_ml_feature' => true
            ],
            [
                'key' => 'alcohol_intake',
                'display_name' => 'Alcohol Consumption',
                'unit_id' => $unit('Number of drinks'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'activity_moderate',
                'display_name' => 'Moderate Recreational Activity',
                'unit_id' => $unit('Minutes'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'activity_vigorous',
                'display_name' => 'Vigorous Recreational Activity',
                'unit_id' => $unit('Minutes'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'sleep_duration',
                'display_name' => 'Sleep Duration',
                'unit_id' => $unit('Hours'),
                'is_ml_feature' => true
            ],

            // Body Metrics
            [
                'key' => 'bmi',
                'display_name' => 'Body Mass Index',
                'unit_id' => $unit('kg/m2'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'weight',
                'display_name' => 'Weight',
                'unit_id' => $unit('kg'),
                'is_ml_feature' => true
            ],
             [
                'key' => 'height',
                'display_name' => 'Height',
                'unit_id' => $unit('cm'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'waist_circumference',
                'display_name' => 'Waist Circumference',
                'unit_id' => $unit('cm'),
                'is_ml_feature' => true
            ],

            // Vitals (Blood Pressure)
            [
                'key' => 'systolic_bp',
                'display_name' => 'Systolic Blood Pressure (Avg)',
                'unit_id' => $unit('mmHg'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'diastolic_bp',
                'display_name' => 'Diastolic Blood Pressure (Avg)',
                'unit_id' => $unit('mmHg'),
                'is_ml_feature' => true
            ],

            // Lab Results
            [
                'key' => 'fasting_glucose',
                'display_name' => 'Fasting Glucose',
                'unit_id' => $unit('mg/dL'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'hba1c',
                'display_name' => 'Glycohemoglobin (HbA1c)',
                'unit_id' => $unit('%'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'ldl_cholesterol',
                'display_name' => 'LDL Cholesterol',
                'unit_id' => $unit('mg/dL'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'hdl_cholesterol',
                'display_name' => 'HDL Cholesterol',
                'unit_id' => $unit('mg/dL'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'triglycerides',
                'display_name' => 'Triglycerides',
                'unit_id' => $unit('mg/dL'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'creatinine',
                'display_name' => 'Serum Creatinine',
                'unit_id' => $unit('mg/dL'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'bun',
                'display_name' => 'Blood Urea Nitrogen',
                'unit_id' => $unit('mg/dL'),
                'is_ml_feature' => true
            ],
            [
                'key' => 'uric_acid',
                'display_name' => 'Uric Acid',
                'unit_id' => $unit('mg/dL'),
                'is_ml_feature' => true
            ],
        ];

        foreach ($variables as $var) {
            HealthVariable::firstOrCreate(
                ['key' => $var['key']], 
                $var
            );
        }
    }
}
