<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangedColTypesForEnrollees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {

            $table->text('last_call_outcome')->after('care_ambassador_id')->nullable()->change();
            $table->text('last_call_outcome_reason')->after('care_ambassador_id')->nullable()->change();

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


        });
    }
}
