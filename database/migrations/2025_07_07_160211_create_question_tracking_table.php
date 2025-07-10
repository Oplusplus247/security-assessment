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
        Schema::create('question_tracking', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('assessment_type');
            $table->string('email');
            $table->enum('status', ['sent', 'pending', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_tracking');
    }
};
