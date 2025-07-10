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
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'factor_id')) {
                $table->foreignId('factor_id')->nullable()->constrained()->onDelete('set null');
            }
            
            if (Schema::hasColumn('questions', 'current_score')) {
                $table->dropColumn('current_score');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
