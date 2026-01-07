<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RiskType;

class RiskTypeSeeder extends Seeder
{
    public function run(): void
    {
        $riskTypes = [
            [
                'key' => 'diabetes',
                'display_name' => 'Type 2 Diabetes'
            ],
            [
                'key' => 'heart_disease',
                'display_name' => 'Heart Disease'
            ],
            [
                'key' => 'hypertension',
                'display_name' => 'Hypertension'
            ],
            [
                'key' => 'kidney_disease',
                'display_name' => 'Kidney Disease'
            ],
        ];

        foreach ($riskTypes as $riskType) {
            RiskType::firstOrCreate(
                ['key' => $riskType['key']],
                $riskType
            );
        }
    }
}

