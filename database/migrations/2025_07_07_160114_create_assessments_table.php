<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained();
            $table->decimal('readiness_level', 3, 1);
            $table->decimal('target_level', 3, 1);
            $table->date('assessment_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assessments');
    }
};
