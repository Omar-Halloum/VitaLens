<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dataset;

class DatasetSeeder extends Seeder
{
    public function run(): void
    {
        Dataset::create([
            'name' => 'NHANES 2013-2014',
            'description' => 'National Health and Nutrition Examination Survey data used for training the risk prediction models.'
        ]);

        Dataset::create([
            'name' => 'NHANES 2017-2018',
            'description' => 'National Health and Nutrition Examination Survey data used for training the risk prediction models.'
        ]);
    }
}
