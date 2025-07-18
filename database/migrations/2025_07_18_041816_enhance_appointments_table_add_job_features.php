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
        Schema::table('appointments', function (Blueprint $table) {
            // Add priority levels (from jobs)
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('status');

            // Add cost tracking fields (from jobs)
            $table->decimal('materials_cost', 12, 2)->default(0)->after('duration');
            $table->decimal('labor_cost', 12, 2)->default(0)->after('materials_cost');
            $table->decimal('total_cost', 12, 2)->default(0)->after('labor_cost');
            $table->decimal('price', 12, 2)->nullable()->after('total_cost');

            // Add time tracking fields (from jobs)
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('price');
            $table->decimal('total_hours', 8, 2)->default(0)->after('estimated_hours');
            $table->timestamp('started_at')->nullable()->after('total_hours');
            $table->timestamp('completed_at')->nullable()->after('started_at');

            // Add scheduled_date for flexibility (from jobs)
            $table->date('scheduled_date')->nullable()->after('completed_at');

            // Add indexes for new fields
            $table->index(['tenant_id', 'priority']);
            $table->index(['tenant_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'priority']);
            $table->dropIndex(['tenant_id', 'scheduled_date']);

            $table->dropColumn([
                'priority',
                'materials_cost',
                'labor_cost',
                'total_cost',
                'price',
                'estimated_hours',
                'total_hours',
                'started_at',
                'completed_at',
                'scheduled_date'
            ]);
        });
    }
};
