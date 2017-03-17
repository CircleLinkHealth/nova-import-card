<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkCareAmbassador extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {

            $table->unsignedInteger('enroller_id')->after('id')->nullable();

            $table->foreign('enroller_id')
                ->references('id')
                ->on('care_ambassadors')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

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

            $table->dropForeign('enroller_id');

        });
    }
}
