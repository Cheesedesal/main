<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_can_submit_a_document_request(): void
    {
        $this->actingAs(User::factory()->create());
        $response = $this->post('/requests', ['requester_name' => 'Mara Santos', 'email' => 'mara@example.com', 'student_id' => '2026-0012', 'document_type' => 'Transcript of Records', 'purpose' => 'Scholarship application', 'copies' => 2]);

        $response->assertRedirect('/requests');
        $this->assertDatabaseHas('document_requests', ['requester_name' => 'Mara Santos', 'status' => 'submitted', 'copies' => 2]);
    }

    public function test_staff_can_update_a_request_status(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff']));
        $this->post('/requests', ['requester_name' => 'Mara Santos', 'email' => 'mara@example.com', 'document_type' => 'Certificate of Enrollment', 'purpose' => 'Internship', 'copies' => 1]);
        $request = DocumentRequest::first();
        $response = $this->patch("/requests/{$request->id}/status", ['status' => 'ready']);

        $response->assertRedirect('/requests');
        $this->assertDatabaseHas('document_requests', ['id' => $request->id, 'status' => 'ready']);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}