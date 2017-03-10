<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCallerIdAndOtherFieldsToEnrollees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {


            $table->unsignedInteger('care_ambassador_id')->after('practice_id')->nullable();
            $table->unsignedInteger('last_call_outcome')->after('care_ambassador_id')->nullable();
            $table->unsignedInteger('last_call_outcome_reason')->after('last_call_outcome')->nullable();

            $table->foreign('care_ambassador_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {


            $table->dropColumn('care_ambassador_id');
            $table->dropColumn('last_call_outcome');
            $table->dropColumn('last_call_outcome_reason');


        });
    }
}
