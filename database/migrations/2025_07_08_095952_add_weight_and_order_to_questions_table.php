<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'weight')) {
                $table->integer('weight')->default(1)->after('question');
            }
            
            if (!Schema::hasColumn('questions', 'order')) {
                $table->integer('order')->nullable()->after('weight');
            }
            
            if (!Schema::hasColumn('questions', 'description')) {
                $table->text('description')->nullable()->after('question');
            }
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['weight', 'order', 'description']);
        });
    }
};