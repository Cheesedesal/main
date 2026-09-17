<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->string('requester_name');
            $table->string('email');
            $table->string('student_id')->nullable();
            $table->string('document_type');
            $table->string('purpose');
            $table->unsignedTinyInteger('copies')->default(1);
            $table->string('status')->default('submitted')->index();
            $table->text('staff_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};