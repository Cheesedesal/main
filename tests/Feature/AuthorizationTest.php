<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_cannot_access_admin_modules(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/activity')->assertForbidden();
        $this->get('/admin/reports')->assertForbidden();
    }

    public function test_regular_users_cannot_change_request_status(): void
    {
        $user = User::factory()->create();
        $request = DocumentRequest::create([
            'created_by' => $user->id,
            'tracking_code' => 'DOC-TEST1234',
            'requester_name' => $user->name,
            'email' => $user->email,
            'document_type' => 'Transcript of Records',
            'purpose' => 'Testing',
            'copies' => 1,
            'status' => 'submitted',
        ]);

        $this->actingAs($user)->patch("/requests/{$request->id}/status", ['status' => 'released'])->assertForbidden();
    }

    public function test_admin_can_access_reports_and_staff_can_access_staff_reports(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/reports')->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'staff']))->get('/staff/reports')->assertOk();
    }
}