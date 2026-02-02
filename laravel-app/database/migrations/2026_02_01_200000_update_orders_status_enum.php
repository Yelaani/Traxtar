<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Simplified workflow: pending → confirmed → shipped → delivered
     * Removed 'processing' status to reduce ambiguity.
     */
    public function up(): void
    {
        // Update enum to remove 'processing' and add 'confirmed'
        // MySQL doesn't support direct enum modification, so we use raw SQL
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
        
        // Update any existing 'processing' orders to 'confirmed'
        DB::table('orders')
            ->where('status', 'processing')
            ->update(['status' => 'confirmed']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum (with processing)
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
        
        // Convert 'confirmed' back to 'processing' if needed
        DB::table('orders')
            ->where('status', 'confirmed')
            ->update(['status' => 'processing']);
    }
};
