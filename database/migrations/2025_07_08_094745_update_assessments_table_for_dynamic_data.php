<?php
// Migration to update assessments table
// Run: php artisan make:migration update_assessments_table_for_dynamic_data

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            }
            
            if (!Schema::hasColumn('assessments', 'status')) {
                $table->string('status')->default('pending');
            }
            
            if (!Schema::hasColumn('assessments', 'total_score')) {
                $table->decimal('total_score', 3, 1)->nullable();
            }
            
            if (Schema::hasColumn('assessments', 'readiness_level')) {
                $table->decimal('readiness_level', 3, 1)->nullable()->default(0)->change();
            } else {
                $table->decimal('readiness_level', 3, 1)->nullable()->default(0);
            }
            
            if (Schema::hasColumn('assessments', 'target_level')) {
                $table->decimal('target_level', 3, 1)->nullable()->default(5.0)->change();
            } else {
                $table->decimal('target_level', 3, 1)->nullable()->default(5.0);
            }
        });
    }

    public function down()
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'status', 'total_score']);
        });
    }
};