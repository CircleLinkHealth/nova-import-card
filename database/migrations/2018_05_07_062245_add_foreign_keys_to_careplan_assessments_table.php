<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCareplanAssessmentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('careplan_assessments', function (Blueprint $table) {
            $table->foreign('provider_approver_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('careplan_assessments', function (Blueprint $table) {
            $table->dropForeign('careplan_assessments_provider_approver_id_foreign');
        });
    }
}
