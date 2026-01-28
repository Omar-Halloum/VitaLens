<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserType;
use App\Models\MedicalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Mockery;
use App\Services\MedicalMetricService;
use App\Services\HealthDataExtractionService;
use App\Services\EngineeredFeatureService;
use App\Services\RiskPredictionService;

class MedicalMetricControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $document;

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
        
        $this->document = MedicalDocument::create([
            'user_id' => $this->user->id,
            'file_path' => 'medical_documents/' . $this->user->id . '/test.pdf',
            'file_type' => 'pdf'
        ]);
    }

    public function test_extract_metrics_success()
    {
        $this->mock(HealthDataExtractionService::class, function ($mock) {
            $mock->shouldReceive('extractFromDocument')
                ->once()
                ->andReturn(['success' => true]);
        });
        
        $this->mock(EngineeredFeatureService::class, function ($mock) {
            $mock->shouldReceive('prepareUserFeatures')->once()->andReturn([]);
        });
        
        $this->mock(RiskPredictionService::class, function ($mock) {
            $mock->shouldReceive('predictUserRisks')->once();
        });
        
        $this->mock(MedicalMetricService::class);

        $response = $this->postJson('/api/extract-metrics', [
            'document_id' => $this->document->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'Metrics extracted and predictions updated successfully',
                'payload' => ['success' => true]
            ]);
    }

    public function test_extract_metrics_failure_validation()
    {
        $response = $this->postJson('/api/extract-metrics', [
            // missing document_id
        ]);

        $response->assertStatus(422);
    }

    public function test_extract_metrics_failure_service_error()
    {
        // Test catch block exception (500)
        $this->mock(HealthDataExtractionService::class, function ($mock) {
            $mock->shouldReceive('extractFromDocument')
                ->andThrow(new \Exception('Extraction Failed'));
        });
        
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);
        $this->mock(MedicalMetricService::class);

        $response = $this->postJson('/api/extract-metrics', [
            'document_id' => $this->document->id
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'Failed to extract metrics: Extraction Failed',
                'payload' => null
            ]);
    }
    
    public function test_extract_metrics_failure_logic_error()
    {
        $this->mock(HealthDataExtractionService::class, function ($mock) {
            $mock->shouldReceive('extractFromDocument')
                ->andReturn(['success' => false, 'message' => 'No data found']);
        });
        
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);
        $this->mock(MedicalMetricService::class);

        $response = $this->postJson('/api/extract-metrics', [
            'document_id' => $this->document->id
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'No data found',
                'payload' => null
            ]);
    }

    public function test_get_user_metrics_success()
    {
        $this->mock(MedicalMetricService::class, function ($mock) {
            $mock->shouldReceive('getMetricsByUser')
                ->once()
                ->andReturn([]);
        });
        
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/medical-metrics');

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Metrics retrieved successfully',
                 'payload' => []
             ]);
    }

    public function test_get_user_metrics_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/medical-metrics');
        $response->assertStatus(401);
    }

    public function test_get_user_metrics_failure_service_error()
    {
        $this->mock(MedicalMetricService::class, function ($mock) {
             $mock->shouldReceive('getMetricsByUser')->andThrow(new \Exception('DB Error'));
        });
        
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/medical-metrics');

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to retrieve metrics: DB Error',
                 'payload' => null
             ]);
    }

    public function test_get_document_metrics_success()
    {
        $this->mock(MedicalMetricService::class, function ($mock) {
            $mock->shouldReceive('getMetricsByDocument')
                ->once()
                ->with($this->document->id)
                ->andReturn([]);
        });
        
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/medical-metrics/document/' . $this->document->id);

        $response->assertStatus(200)
             ->assertJson([
                 'status' => 'Document metrics retrieved successfully',
                 'payload' => []
             ]);
    }

    public function test_get_document_metrics_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/medical-metrics/document/' . $this->document->id);
        $response->assertStatus(401);
    }

    public function test_get_document_metrics_failure_service_error()
    {
        $this->mock(MedicalMetricService::class, function ($mock) {
            $mock->shouldReceive('getMetricsByDocument')->andThrow(new \Exception('Err'));
        });
        
        $this->mock(HealthDataExtractionService::class);
        $this->mock(EngineeredFeatureService::class);
        $this->mock(RiskPredictionService::class);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
             ->getJson('/api/v1/medical-metrics/document/' . $this->document->id);

        $response->assertStatus(500)
             ->assertJson([
                 'status' => 'Failed to retrieve document metrics: Err',
                 'payload' => null
             ]);
    }
}
