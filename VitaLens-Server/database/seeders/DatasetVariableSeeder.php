<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dataset;
use App\Models\HealthVariable;
use App\Models\DatasetVariable;

class DatasetVariableSeeder extends Seeder
{
    public function run(): void
    {
        $dataset = Dataset::where('name', 'NHANES 2013-2014/2017-2018 (Combined)')->first();

        $getVariable = fn($key) => HealthVariable::where('key', $key)->value('id');

        // Mapping CSV column names to health_variable keys
        $mappings = [
            // Demographics
            'RIDAGEYR' => 'age',
            'RIAGENDR' => 'gender',
            
            // Habits & Lifestyle
            'SMQ020' => 'smoking_status',
            'ALQ130' => 'alcohol_intake',
            'PAD675' => 'activity_moderate',
            'PAD660' => 'activity_vigorous',
            'SLD010H' => 'sleep_duration',
            
            // Body Measures
            'BMXBMI' => 'bmi',
            'BMXWAIST' => 'waist_circumference',

            // Vitals
            'avg_systolic' => 'systolic_bp', 
            'avg_diastolic' => 'diastolic_bp',
            
            // Lab Results
            'LBXGLU' => 'fasting_glucose',
            'LBXGH' => 'hba1c',
            'LBDLDL' => 'ldl_cholesterol',
            'LBDHDD' => 'hdl_cholesterol',
            'LBXTR' => 'triglycerides',
            'LBXSCR' => 'creatinine',
            'LBXSBU' => 'bun',
            'LBXSUA' => 'uric_acid',
        ];

        foreach ($mappings as $columnName => $variableKey) {
            $variableId = $getVariable($variableKey);

            if ($variableId) {
                DatasetVariable::firstOrCreate(
                    [
                        'dataset_id' => $dataset->id,
                        'column_name' => $columnName
                    ],
                    [
                        'health_variable_id' => $variableId
                    ]
                );
            }
        }
    }
}