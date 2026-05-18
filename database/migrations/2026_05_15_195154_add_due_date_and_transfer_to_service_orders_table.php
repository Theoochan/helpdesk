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
        Schema::table('service_orders', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('done_at');
            $table->foreignId('transfer_requested_to_id')->nullable()->after('due_date')->constrained('users')->nullOnDelete();
            $table->text('transfer_note')->nullable()->after('transfer_requested_to_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['transfer_requested_to_id']);
            $table->dropColumn(['due_date', 'transfer_requested_to_id', 'transfer_note']);
        });
    }
};
