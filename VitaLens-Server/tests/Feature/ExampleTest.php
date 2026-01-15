<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    
    public function test_example(): void
    {
        $response = $this->get('/api/error');
        
        $response->assertStatus(401);
    }
}
