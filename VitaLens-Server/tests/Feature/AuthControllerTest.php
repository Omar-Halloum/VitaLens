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
}
