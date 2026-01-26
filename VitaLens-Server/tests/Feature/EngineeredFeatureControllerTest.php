<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Mockery;
use App\Services\EngineeredFeatureService;

class EngineeredFeatureControllerTest extends TestCase
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

    public function test_engineer_features_success()
    {
        $this->mock(EngineeredFeatureService::class, function ($mock) {
            $mock->shouldReceive('prepareUserFeatures')
                ->once()
                ->andReturn(['feature1' => 10, 'feature2' => 20]);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/engineer-features');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'Features engineered successfully',
                'payload' => ['feature1' => 10]
            ]);
    }

    public function test_engineer_features_failure_unauthenticated()
    {
        $response = $this->postJson('/api/v1/engineer-features');
        $response->assertStatus(401);
    }

    public function test_engineer_features_failure_service_error()
    {
        $this->mock(EngineeredFeatureService::class, function ($mock) {
            $mock->shouldReceive('prepareUserFeatures')->andThrow(new \Exception('Calc Error'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/engineer-features');

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'Failed to engineer features: Calc Error',
                'payload' => null
            ]);
    }

    public function test_get_user_features_success()
    {
        $this->mock(EngineeredFeatureService::class, function ($mock) {
            $mock->shouldReceive('getUserFeatures')
                ->once()
                ->andReturn([]);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/get-engineered-features');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Features retrieved successfully',
                 'payload' => []
             ]);
    }

    public function test_get_user_features_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/get-engineered-features');
        $response->assertStatus(401);
    }

    public function test_get_user_features_failure_service_error()
    {
        $this->mock(EngineeredFeatureService::class, function ($mock) {
             $mock->shouldReceive('getUserFeatures')->andThrow(new \Exception('DB Error'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/get-engineered-features');

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to retrieve features: DB Error',
                 'payload' => null
             ]);
    }

    public function test_get_feature_history_success_all()
    {
        $this->mock(EngineeredFeatureService::class, function ($mock) {
            $mock->shouldReceive('getFeatureHistory')
                ->once()
                ->with(Mockery::any(), null, null, null)
                ->andReturn([]);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/feature-history');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Feature history retrieved successfully',
                 'payload' => []
             ]);
    }
    
    public function test_get_feature_history_success_specific()
    {
        $this->mock(EngineeredFeatureService::class, function ($mock) {
            $mock->shouldReceive('getFeatureHistory')
                ->once()
                ->with(Mockery::any(), 'bmi', null, null)
                ->andReturn([]);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/feature-history/bmi');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Feature history retrieved successfully',
             ]);
    }

    public function test_get_feature_history_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/feature-history');
        $response->assertStatus(401);
    }

    public function test_get_feature_history_failure_service_error()
    {
        $this->mock(EngineeredFeatureService::class, function ($mock) {
            $mock->shouldReceive('getFeatureHistory')->andThrow(new \Exception('Err'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/feature-history');

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to retrieve feature history: Err',
                 'payload' => null
             ]);
    }
}
