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
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained();
            $table->string('action');
            $table->string('department');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('corrective_actions');
    }
};
