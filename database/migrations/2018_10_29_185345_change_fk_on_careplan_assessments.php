<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFkOnCareplanAssessments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('careplan_assessments', function (Blueprint $table) {
//            $table->dropForeign('careplan_assessments_careplan_id_foreign');

            $table->foreign('careplan_id')
                  ->references('id')
                  ->on('users')
                  ->odDelete('cascade')
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
        //
    }
}
