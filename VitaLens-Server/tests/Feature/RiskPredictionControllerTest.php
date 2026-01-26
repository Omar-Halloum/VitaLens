<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Mockery;
use App\Models\RiskType;
use App\Services\RiskPredictionService;

class RiskPredictionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        UserType::create(['name' => 'Admin']);
        UserType::create(['name' => 'User']);
        
        RiskType::create(['key' => 'diabetes', 'display_name' => 'Type 2 Diabetes']);

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

    public function test_predict_risks_success()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('predictUserRisks')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Risks predicted successfully',
                    'predictions' => []
                ]);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/predict-risks');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'Risks predicted successfully',
                'payload' => [
                    'success' => true
                ]
            ]);
    }

    public function test_predict_risks_failure_unauthenticated()
    {
        $response = $this->postJson('/api/v1/predict-risks');
        $response->assertStatus(401);
    }

    public function test_predict_risks_failure_service_error()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('predictUserRisks')->andThrow(new \Exception('Prediction Error'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->postJson('/api/v1/predict-risks');

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'Failed to predict risks: Prediction Error',
                'payload' => null
            ]);
    }

    public function test_get_user_predictions_success()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('getUserPredictions')
                ->once()
                ->andReturn([]);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/risk-predictions');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Risk predictions retrieved successfully',
                 'payload' => []
             ]);
    }

    public function test_get_user_predictions_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/risk-predictions');
        $response->assertStatus(401);
    }

    public function test_get_user_predictions_failure_service_error()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
             $mock->shouldReceive('getUserPredictions')->andThrow(new \Exception('DB Error'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/risk-predictions');

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to retrieve predictions: DB Error',
                 'payload' => null
             ]);
    }

    public function test_get_risk_prediction_success()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('getRiskPrediction')
                ->once()
                ->with(Mockery::any(), 'diabetes')
                ->andReturn(['risk' => 'low']);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/risk-predictions/diabetes');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Risk prediction retrieved successfully',
                 'payload' => ['risk' => 'low']
             ]);
    }

    public function test_get_risk_prediction_failure_not_found()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('getRiskPrediction')
                ->andReturn(null);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/risk-predictions/diabetes'); // 'diabetes' exists in DB

        $response->assertStatus(404)
             ->assertJson([
                 'status' => 'Risk prediction not found',
                 'payload' => null
             ]);
    }

    public function test_get_risk_prediction_failure_service_error()
    {
         $this->mock(RiskPredictionService::class, function ($mock) {
             $mock->shouldReceive('getRiskPrediction')->andThrow(new \Exception('Err'));
         });

         $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
              ->getJson('/api/v1/risk-predictions/diabetes');

         $response->assertStatus(500)
              ->assertJson([
                  'status' => 'Failed to retrieve prediction: Err',
                  'payload' => null
              ]);
    }

    public function test_get_risk_factors_success()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('getRiskFactors')
                ->with('diabetes')
                ->andReturn(['age', 'weight']);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/risk-factors/diabetes');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Risk factors retrieved successfully',
                 'payload' => ['age', 'weight']
             ]);
    }

    public function test_get_risk_factors_failure_not_found()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('getRiskFactors')
                ->andReturn([]);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/risk-factors/unknown');

        $response->assertStatus(404)
             ->assertJson([
                 'status' => 'Risk type not found',
                 'payload' => null
             ]);
    }

    public function test_get_risk_factors_failure_service_error()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('getRiskFactors')->andThrow(new \Exception('Err'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/risk-factors/diabetes');

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to retrieve risk factors: Err',
                 'payload' => null
             ]);
    }

    public function test_check_data_sufficiency_success()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('checkDataSufficiency')
                ->once()
                ->andReturn(['sufficient' => true]);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/check-data-sufficiency');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Data sufficiency checked successfully',
                 'payload' => ['sufficient' => true]
             ]);
    }

    public function test_check_data_sufficiency_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/check-data-sufficiency');
        $response->assertStatus(401);
    }

    public function test_check_data_sufficiency_failure_service_error()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('checkDataSufficiency')->andThrow(new \Exception('Err'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/check-data-sufficiency');

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to check data sufficiency: Err',
                 'payload' => null
             ]);
    }

    public function test_get_risk_history_success()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('getRiskHistory')
                ->once()
                ->andReturn([]);
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/risk-history');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Risk history retrieved successfully',
                 'payload' => []
             ]);
    }

    public function test_get_risk_history_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/risk-history');
        $response->assertStatus(401);
    }

    public function test_get_risk_history_failure_service_error()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('getRiskHistory')->andThrow(new \Exception('Err'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/risk-history');

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to retrieve risk history: Err',
                 'payload' => null
             ]);
    }

    public function test_store_predictions_success()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('storePredictions')
                ->once();
        });

        $response = $this->postJson('/api/store-predictions', [
            'user_id' => $this->user->id,
            'predictions' => [
                [
                    'risk_type' => 'diabetes',
                    'probability' => 0.5,
                    'confidence_level' => 'medium'
                ]
            ]
        ]);

        $response->assertStatus(201)
             ->assertJson([
                 'status' => 'Predictions stored successfully',
                 'payload' => null
             ]);
    }

    public function test_store_predictions_failure_validation()
    {
        $response = $this->postJson('/api/store-predictions', [
            // missing user_id and predictions
        ]);

        $response->assertStatus(422);
    }

    public function test_store_predictions_failure_service_error()
    {
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('storePredictions')->andThrow(new \Exception('Err'));
        });

        $response = $this->postJson('/api/store-predictions', [
            'user_id' => $this->user->id,
            'predictions' => [
                [
                    'risk_type' => 'diabetes',
                    'confidence_level' => 'high'
                ]
            ]
        ]);

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to store predictions: Err',
                 'payload' => null
             ]);
    }
}
