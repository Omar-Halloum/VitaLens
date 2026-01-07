<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserTypesSeeder::class); 
        $this->call(UnitSeeder::class);
        $this->call(DatasetSeeder::class);
        
        // Tables with single dependencies
        $this->call(HealthVariableSeeder::class);
        $this->call(FeatureDefinitionSeeder::class);
        $this->call(RiskTypeSeeder::class);
        
        // Tables with multiple dependencies
        $this->call(RiskRequirementSeeder::class);
        $this->call(DatasetVariableSeeder::class); 
    }
}
