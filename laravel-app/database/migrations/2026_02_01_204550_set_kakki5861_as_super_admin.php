<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Set kakki5861@gmail.com as the only super admin.
     */
    public function up(): void
    {
        // First, set all current super_admins (except the target email) to 'admin'
        DB::table('users')
            ->where('role', 'super_admin')
            ->where('email', '!=', 'kakki5861@gmail.com')
            ->update(['role' => 'admin']);

        // Then, set kakki5861@gmail.com to super_admin
        DB::table('users')
            ->where('email', 'kakki5861@gmail.com')
            ->update(['role' => 'super_admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert kakki5861@gmail.com back to admin
        DB::table('users')
            ->where('email', 'kakki5861@gmail.com')
            ->update(['role' => 'admin']);
    }
};
