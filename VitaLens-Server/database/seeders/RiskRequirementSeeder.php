<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskType;
use App\Models\FeatureDefinition;
use App\Models\RiskRequirement;

class RiskRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $getRiskType = fn($key) => RiskType::where('key', $key)->value('id');
        $getFeature = fn($name) => FeatureDefinition::where('feature_name', $name)->value('id');

        $requirements = [
            // Type 2 Diabetes
            [
                'risk_type_id' => $getRiskType('type_2_diabetes'), 
                'feature_definition_id' => $getFeature('systolic_bp'), 
                'is_required' => true
            ],
            [
                'risk_type_id' => $getRiskType('type_2_diabetes'), 
                'feature_definition_id' => $getFeature('activity_moderate'), 
                'is_required' => false 
            ],
            [
                'risk_type_id' => $getRiskType('type_2_diabetes'), 
                'feature_definition_id' => $getFeature('fasting_glucose'), 
                'is_required' => true
            ],
            [
                'risk_type_id' => $getRiskType('type_2_diabetes'), 
                'feature_definition_id' => $getFeature('bmi'), 
                'is_required' => true
            ],

            // Heart Disease
            [
                'risk_type_id' => $getRiskType('heart_disease'), 
                'feature_definition_id' => $getFeature('systolic_bp'), 
                'is_required' => true
            ],
            [
                'risk_type_id' => $getRiskType('heart_disease'), 
                'feature_definition_id' => $getFeature('diastolic_bp'), 
                'is_required' => true
            ],
            [
                'risk_type_id' => $getRiskType('heart_disease'), 
                'feature_definition_id' => $getFeature('ldl_cholesterol'), 
                'is_required' => true
            ],
            [
                'risk_type_id' => $getRiskType('heart_disease'), 
                'feature_definition_id' => $getFeature('smoking_status'), 
                'is_required' => true
            ],

            // Hypertension
            [
                'risk_type_id' => $getRiskType('hypertension'), 
                'feature_definition_id' => $getFeature('systolic_bp'), 
                'is_required' => true
            ],
            [
                'risk_type_id' => $getRiskType('hypertension'), 
                'feature_definition_id' => $getFeature('diastolic_bp'), 
                'is_required' => true
            ],
            [
                'risk_type_id' => $getRiskType('hypertension'), 
                'feature_definition_id' => $getFeature('age'), 
                'is_required' => true
            ],
            [
                'risk_type_id' => $getRiskType('hypertension'), 
                'feature_definition_id' => $getFeature('bmi'), 
                'is_required' => false
            ],
            [
                'risk_type_id' => $getRiskType('hypertension'), 
                'feature_definition_id' => $getFeature('activity_moderate'), 
                'is_required' => false
            ],

            // Chronic Kidney Disease
            [
                'risk_type_id' => $getRiskType('chronic_kidney_disease'), 
                'feature_definition_id' => $getFeature('systolic_bp'), 
                'is_required' => true
            ],
            [
                'risk_type_id' => $getRiskType('chronic_kidney_disease'), 
                'feature_definition_id' => $getFeature('creatinine'), 
                'is_required' => true
            ],
             [
                'risk_type_id' => $getRiskType('chronic_kidney_disease'), 
                'feature_definition_id' => $getFeature('bun'), 
                'is_required' => true
            ],
        ];

        foreach ($requirements as $requirement) {
            // Only insert if both IDs were found
            if ($requirement['risk_type_id'] && $requirement['feature_definition_id']) {
                RiskRequirement::firstOrCreate(
                    [
                        'risk_type_id' => $requirement['risk_type_id'],
                        'feature_definition_id' => $requirement['feature_definition_id']
                    ],
                    ['is_required' => $requirement['is_required']]
                );
            }
        }
    }
}