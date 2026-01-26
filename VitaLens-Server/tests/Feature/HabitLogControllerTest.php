<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserType;
use App\Models\HabitLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Mockery;
use App\Services\HabitLogService;
use App\Services\HabitMetricService;
use App\Services\HealthDataExtractionService;
use App\Services\EngineeredFeatureService;
use App\Services\RiskPredictionService;

class HabitLogControllerTest extends TestCase
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


    public function test_store_habit_success()
    {
        $this->mock(HabitLogService::class, function ($mock) {
            $mock->shouldReceive('createLog')
                ->once()
                ->andReturn(new HabitLog(['id' => 1]));
        });

        $this->mock(HealthDataExtractionService::class, function ($mock) {
            $mock->shouldReceive('extractFromHabitLog')
                ->once()
                ->andReturn([
                    'success' => true, 
                    'metrics_count' => 2, 
                    'ai_insight' => 'Good job!'
                ]);
        });

        $this->mock(EngineeredFeatureService::class, function ($mock) {
            $mock->shouldReceive('prepareUserFeatures')->once()->andReturn([]);
        });

        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('predictUserRisks')->once();
        });

        $this->mock(HabitMetricService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/log-habit', [
                 'habit_text' => 'I drank 2L of water today'
             ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'Habit logged, metrics extracted, and predictions updated successfully',
            ])
            ->assertJsonStructure([
                'payload' => ['habit_log_id', 'metrics_count', 'ai_insight']
            ]);
    }

    public function test_store_habit_failure_validation()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/log-habit', [
                 // missing habit_text
             ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['habit_text']);
    }

    public function test_store_habit_failure_extraction_error()
    {
        // Mock Service failure scenario
        $this->mock(HabitLogService::class, function ($mock) {
            $mock->shouldReceive('createLog')->andReturn(new HabitLog(['id' => 1]));
        });

        $this->mock(HealthDataExtractionService::class, function ($mock) {
            $mock->shouldReceive('extractFromHabitLog')
                ->andReturn([
                    'success' => false, 
                    'message' => 'Could not extract data'
                ]);
        });
        
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);
        $this->mock(HabitMetricService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/log-habit', [
                 'habit_text' => 'Unclear text'
             ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'Could not extract data'
            ]);
    }


    public function test_get_user_logs_success()
    {
        $this->mock(HabitLogService::class, function ($mock) {
            $mock->shouldReceive('getUserLogs')
                ->once()
                ->andReturn([]);
        });
        
        $this->mock(HabitMetricService::class);
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/habit-logs');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'Habit logs retrieved successfully',
                'payload' => []
            ]);
    }

    public function test_get_user_logs_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/habit-logs');
        $response->assertStatus(401);
    }

    public function test_get_user_logs_failure_service_error()
    {
        $this->mock(HabitLogService::class, function ($mock) {
            $mock->shouldReceive('getUserLogs')->andThrow(new \Exception('DB Error'));
        });
        
        $this->mock(HabitMetricService::class);
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/habit-logs');

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'Failed to retrieve habit logs: DB Error',
                'payload' => null
            ]);
    }

    public function test_get_user_habit_metrics_success()
    {
        $this->mock(HabitMetricService::class, function ($mock) {
            $mock->shouldReceive('getMetricsByUser')
                ->once()
                ->andReturn([]);
        });
        
        $this->mock(HabitLogService::class);
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/habit-metrics');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Habit metrics retrieved successfully',
                 'payload' => []
             ]);
    }

    public function test_get_user_habit_metrics_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/habit-metrics');
        $response->assertStatus(401);
    }

    public function test_get_user_habit_metrics_failure_service_error()
    {
        $this->mock(HabitMetricService::class, function ($mock) {
            $mock->shouldReceive('getMetricsByUser')->andThrow(new \Exception('Metric Error'));
        });

        $this->mock(HabitLogService::class);
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/habit-metrics');

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to retrieve habit metrics: Metric Error',
                 'payload' => null
             ]);
    }

    public function test_get_log_metrics_success()
    {
        $this->mock(HabitMetricService::class, function ($mock) {
            $mock->shouldReceive('getMetricsByLog')
                ->once()
                ->with('123')
                ->andReturn([]);
        });
        
        $this->mock(HabitLogService::class);
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/habit-log-metrics/123');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Log metrics retrieved successfully',
                 'payload' => []
             ]);
    }

    public function test_get_log_metrics_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/habit-log-metrics/123');
        $response->assertStatus(401);
    }

    public function test_get_log_metrics_failure_service_error()
    {
        $this->mock(HabitMetricService::class, function ($mock) {
            $mock->shouldReceive('getMetricsByLog')->andThrow(new \Exception('Not found'));
        });
        
        $this->mock(HabitLogService::class);
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/habit-log-metrics/123');

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to retrieve log metrics: Not found',
                 'payload' => null
             ]);
    }
}
