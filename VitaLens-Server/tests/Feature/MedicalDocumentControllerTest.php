<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserType;
use App\Models\MedicalDocument; // Assuming this model exists
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Mockery;
use App\Services\MedicalDocumentService;

class MedicalDocumentControllerTest extends TestCase
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

    public function test_upload_document_success()
    {
        Storage::fake('s3'); // Fake S3 storage
        $file = UploadedFile::fake()->create('report.pdf', 1000, 'application/pdf');

        // Mock the service to avoid OCR/external calls
        $this->mock(MedicalDocumentService::class, function ($mock) use ($file) {
            $mock->shouldReceive('addDocument')
                ->once()
                ->andReturn(new MedicalDocument([
                    'id' => 1,
                    'user_id' => $this->user->id,
                    'file_path' => 'medical_documents/' . $this->user->id . '/report.pdf',
                    'file_type' => 'pdf',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]));
        });

        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])
            ->postJson('/api/v1/upload-documents', [
                'document' => $file
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'Document uploaded successfully',
            ]);
    }

    public function test_upload_document_failure_missing_file()
    {
        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])
            ->postJson('/api/v1/upload-documents', [
                // 'document' is missing
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document']);
    }

    public function test_upload_document_failure_invalid_format()
    {
        Storage::fake('s3');
        $file = UploadedFile::fake()->create('script.sh', 100); // Invalid extension

        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])
            ->postJson('/api/v1/upload-documents', [
                'document' => $file
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document']);
    }

    public function test_upload_document_failure_service_error()
    {
        Storage::fake('s3');
        $file = UploadedFile::fake()->create('report.pdf', 1000, 'application/pdf');
        
        $this->mock(MedicalDocumentService::class, function ($mock) {
            $mock->shouldReceive('addDocument')
                ->andThrow(new \Exception('OCR service unavailable'));
        });

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/v1/upload-documents', ['document' => $file]);

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'failure',
                'payload' => 'OCR service unavailable'
            ]);
    }

    public function test_get_documents_success()
    {
        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])
            ->getJson('/api/v1/get-documents');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'Documents retrieved successfully'
            ]);
    }

    public function test_get_documents_failure_unauthenticated()
    {
        $response = $this->getJson('/api/v1/get-documents');

        $response->assertStatus(401);
    }

    public function test_get_documents_failure_service_error()
    {
        $this->mock(MedicalDocumentService::class, function ($mock) {
            $mock->shouldReceive('getUserDocuments')
                ->andThrow(new \Exception('Service unavailable'));
        });

        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])
            ->getJson('/api/v1/get-documents');

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'failure',
                'payload' => 'Service unavailable'
            ]);
    }
}
