<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Modify role enum to include super_admin
            // Note: MySQL doesn't support modifying enum directly, so we'll use raw SQL
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'customer', 'super_admin') DEFAULT 'customer'");
            
            // Add is_active field for soft deactivation
            $table->boolean('is_active')->default(true)->after('role');
            
            // Add invitation tracking fields
            $table->foreignId('invited_by')->nullable()->after('is_active')->constrained('users')->onDelete('set null');
            $table->timestamp('invited_at')->nullable()->after('invited_by');
            
            // Add indexes
            $table->index('is_active');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['invited_by']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['role']);
            $table->dropColumn(['is_active', 'invited_by', 'invited_at']);
            
            // Revert role enum to original
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'customer') DEFAULT 'customer'");
        });
    }
};
