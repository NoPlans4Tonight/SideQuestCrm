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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasIndex('customers', 'customers_tenant_id_created_at_index')) {
                $table->index(['tenant_id', 'created_at']);
            }
            if (!Schema::hasIndex('customers', 'customers_tenant_id_first_name_last_name_index')) {
                $table->index(['tenant_id', 'first_name', 'last_name']);
            }
            if (!Schema::hasIndex('customers', 'customers_tenant_id_phone_index')) {
                $table->index(['tenant_id', 'phone']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'created_at']);
            $table->dropIndex(['tenant_id', 'first_name', 'last_name']);
            $table->dropIndex(['tenant_id', 'phone']);
        });
    }
};
