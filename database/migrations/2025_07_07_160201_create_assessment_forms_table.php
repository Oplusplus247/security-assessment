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
        Schema::create('assessment_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('questions');
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assessment_forms');
    }
};
