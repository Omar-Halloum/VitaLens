<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserType;

class UserTypesSeeder extends Seeder
{
    public function run(): void
    {
        UserType::create(['name' => 'Admin']);
        UserType::create(['name' => 'User']);
        UserType::create(['name' => 'Clinic Manager']);
    }
}
