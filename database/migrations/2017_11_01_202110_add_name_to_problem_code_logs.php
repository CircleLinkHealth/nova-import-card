<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToProblemCodeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_problem_code_logs', function (Blueprint $table) {
            $table->string('name', '150')->nullable()->after('code');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_problem_code_logs', function (Blueprint $table) {
            $table->dropColumn('name');

        });
    }
}
