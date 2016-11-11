<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdVendorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_vendors', function (Blueprint $table) {
            $table->foreign('program_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_vendors', function (Blueprint $table) {
            $table->dropForeign('ccd_vendors_program_id_foreign');
        });
    }

}
