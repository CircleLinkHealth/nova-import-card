<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddProgramToVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_vendors', function (Blueprint $table) {
            $table->unsignedInteger('program_id')->after('id')->nullable();

            $table->foreign('program_id')
                ->references('id')
                ->on('wp_blogs')
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
        Schema::table('ccd_vendors', function (Blueprint $table) {
            //
        });
    }
}
