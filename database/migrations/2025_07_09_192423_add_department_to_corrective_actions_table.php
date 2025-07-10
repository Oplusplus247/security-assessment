<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('corrective_actions', function (Blueprint $table) {
            if (!Schema::hasColumn('corrective_actions', 'department')) {
                $table->string('department')->nullable()->after('action');
            }
        });
    }

    public function down()
    {
        Schema::table('corrective_actions', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }
}
?>