<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAaaaHello extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('AAAAAAA_hello')) {
            Schema::table('AAAAAAA_hello', function (Blueprint $table) {
                $table->drop();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('AAAAAAA_hello', function (Blueprint $table) {
            //
        });
    }
}
