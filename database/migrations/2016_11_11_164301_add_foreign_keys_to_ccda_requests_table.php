<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdaRequestsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccda_requests', function (Blueprint $table) {
            $table->foreign('ccda_id')->references('id')->on('ccdas')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccda_requests', function (Blueprint $table) {
            $table->dropForeign('ccda_requests_ccda_id_foreign');
        });
    }

}
