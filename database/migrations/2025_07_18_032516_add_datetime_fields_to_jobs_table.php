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
        Schema::table('crm_jobs', function (Blueprint $table) {
            // Add datetime fields for better calendar integration
            $table->timestamp('start_time')->nullable()->after('scheduled_date');
            $table->timestamp('end_time')->nullable()->after('start_time');
            $table->integer('duration')->nullable()->after('end_time'); // in minutes

            // Add index for datetime queries
            $table->index(['tenant_id', 'start_time']);
            $table->index(['tenant_id', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_jobs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'start_time']);
            $table->dropIndex(['tenant_id', 'end_time']);
            $table->dropColumn(['start_time', 'end_time', 'duration']);
        });
    }
};
