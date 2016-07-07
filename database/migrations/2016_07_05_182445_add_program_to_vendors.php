<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
                ->references('blog_id')
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
