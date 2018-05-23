<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPracticeIdToCareAmbassadorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {
            $table->unsignedInteger('practice_id')
                  ->after('enroller_id')
                  ->nullable();

            $table->foreign('practice_id')
                  ->references('id')
                  ->on('practices')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {

            $table->dropColumn('practice_id');
            $table->dropForeign(['practice_id']);

        });
    }
}
