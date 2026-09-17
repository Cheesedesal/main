<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\RequestFile;
use App\Models\User;
use App\Notifications\RequestStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RequestExpansionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_request_records_history_and_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/requests', [
            'requester_name' => $user->name,
            'email' => $user->email,
            'document_type' => 'Transcript of Records',
            'purpose' => 'Scholarship',
            'copies' => 1,
        ])->assertRedirect('/requests');

        $request = DocumentRequest::first();
        $this->assertDatabaseHas('request_status_histories', ['document_request_id' => $request->id, 'status' => 'submitted']);
        Notification::assertSentTo($user, RequestStatusUpdated::class);
    }

    public function test_only_staff_can_change_status_and_users_can_view_their_request(): void
    {
        $user = User::factory()->create();
        $request = DocumentRequest::create(['created_by' => $user->id, 'tracking_code' => 'DOC-EXPAND1', 'requester_name' => $user->name, 'email' => $user->email, 'document_type' => 'Certificate', 'purpose' => 'Testing', 'copies' => 1, 'status' => 'submitted']);

        $this->actingAs($user)->get("/requests/{$request->id}")->assertOk();
        $this->actingAs($user)->patch("/requests/{$request->id}/status", ['status' => 'ready'])->assertForbidden();
    }

    public function test_admin_can_archive_and_restore_a_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = DocumentRequest::create(['created_by' => $admin->id, 'tracking_code' => 'DOC-EXPAND2', 'requester_name' => $admin->name, 'email' => $admin->email, 'document_type' => 'Certificate', 'purpose' => 'Testing', 'copies' => 1, 'status' => 'submitted']);

        $this->actingAs($admin)->delete("/requests/{$request->id}")->assertRedirect('/requests');
        $this->assertSoftDeleted('document_requests', ['id' => $request->id]);
        $this->actingAs($admin)->patch("/requests/{$request->id}/restore")->assertRedirect('/requests');
        $this->assertDatabaseHas('document_requests', ['id' => $request->id, 'deleted_at' => null]);
    }

    public function test_request_files_are_private_and_role_checked(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $request = DocumentRequest::create(['created_by' => $owner->id, 'tracking_code' => 'DOC-EXPAND3', 'requester_name' => $owner->name, 'email' => $owner->email, 'document_type' => 'Certificate', 'purpose' => 'Testing', 'copies' => 1, 'status' => 'submitted']);
        $path = 'document-requests/'.$request->id.'/proof.pdf';
        Storage::disk('local')->put($path, 'test');
        $file = RequestFile::create(['document_request_id' => $request->id, 'uploaded_by' => $owner->id, 'disk' => 'local', 'path' => $path, 'original_name' => 'proof.pdf', 'mime_type' => 'application/pdf', 'size' => 4]);

        $this->actingAs($otherUser)->get("/request-files/{$file->id}")->assertForbidden();
        $this->actingAs($owner)->get("/request-files/{$file->id}")->assertOk();
    }
}
