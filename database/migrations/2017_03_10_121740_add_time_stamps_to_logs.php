<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeStampsToLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {

            $table->timestamps();
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

            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}
