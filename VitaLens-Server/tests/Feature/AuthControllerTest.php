<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed required foreign key dependencies
        UserType::create(['name' => 'Admin']);
        UserType::create(['name' => 'User']);
    }

    public function test_register_success()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@vitalens.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'gender' => '1',
            'birth_date' => '2000-01-01',
            'weight' => 70,
            'height' => 175,
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'User created successfully',
            ])
            ->assertJsonStructure([
                'payload' => [
                    'id',
                    'name',
                    'email',
                    'token'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@vitalens.com',
            'name' => 'Test User'
        ]);
    }

    public function test_register_failure_validation()
    {
        $payload = [
            'name' => 'Test User',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'gender', 'birth_date', 'weight', 'height']);
    }

    public function test_login_success()
    {
        User::create([
            'name' => 'Login User',
            'email' => 'login@vitalens.com',
            'password' => Hash::make('password123'),
            'gender' => '1',
            'birth_date' => '1990-01-01',
            'user_type_id' => 2
        ]);

        $payload = [
            'email' => 'login@vitalens.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'Login successful'
            ])
            ->assertJsonStructure([
                'payload' => [
                    'id',
                    'name',
                    'email',
                    'token'
                ]
            ]);
    }
}
