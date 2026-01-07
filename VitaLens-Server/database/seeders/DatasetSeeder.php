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
            'name' => 'NHANES 2013-2014/2017-2018 (Combined)', 
            'description' => 'National Health and Nutrition Examination Survey. Merged data from 2013-2014 and 2017-2018 cycles used for training.'
        ]);
    }
}
