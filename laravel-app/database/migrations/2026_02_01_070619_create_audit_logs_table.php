<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // e.g., 'user.created', 'user.updated', 'api_token.revoked'
            $table->string('model_type')->nullable(); // e.g., 'App\Models\User'
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the affected model
            $table->string('severity')->default('info'); // info, warning, error, critical
            $table->string('type')->nullable(); // Authentication, User Management, API Access, etc.
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data (IP, user agent, etc.)
            $table->string('ip_address', 45)->nullable(); // IPv6 support
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index(['type', 'severity']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
