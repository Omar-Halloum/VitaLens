<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Mockery;
use App\Services\UserService;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        UserType::create(['name' => 'Admin']);
        UserType::create(['name' => 'User']);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@vitalens.com',
            'password' => Hash::make('password'),
            'gender' => '1',
            'birth_date' => '2000-01-01',
            'user_type_id' => 2
        ]);
        
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_get_profile_success()
    {
        $this->mock(UserService::class, function ($mock) {
            $mock->shouldReceive('getProfile')
                ->once()
                ->andReturn($this->user);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'User profile retrieved successfully',
                'payload' => [
                    'id' => $this->user->id,
                    'email' => 'test@vitalens.com'
                ]
            ]);
    }

    public function test_get_profile_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/profile');
        $response->assertStatus(401);
    }

    public function test_get_profile_failure_service_error()
    {
        $this->mock(UserService::class, function ($mock) {
            $mock->shouldReceive('getProfile')->andThrow(new \Exception('DB Error'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/profile');

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'Failed to retrieve profile: DB Error',
                'payload' => null
            ]);
    }

    public function test_update_profile_success()
    {
        $this->mock(UserService::class, function ($mock) {
            $mock->shouldReceive('updateProfile')
                ->once();
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/update-profile', [
                 'weight' => 75.5,
                 'height' => 180
             ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'Profile updated and health analysis triggered successfully',
                'payload' => [
                    'id' => $this->user->id
                ]
            ]);
    }

    public function test_update_profile_failure_validation()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/update-profile', [
                 'weight' => 'heavy' // Invalid numeric
             ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight']);
    }

    public function test_update_profile_failure_service_error()
    {
        $this->mock(UserService::class, function ($mock) {
             $mock->shouldReceive('updateProfile')->andThrow(new \Exception('Update Failed'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/update-profile', [
                 'name' => 'New Name'
             ]);

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to update profile: Update Failed',
                 'payload' => null
             ]);
    }
}
