<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToNurseMonthlySummariesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nurse_monthly_summaries', function (Blueprint $table) {
            $table->foreign('nurse_id')->references('id')->on('nurse_info')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_monthly_summaries', function (Blueprint $table) {
            $table->dropForeign('nurse_monthly_summaries_nurse_id_foreign');
        });
    }

}
