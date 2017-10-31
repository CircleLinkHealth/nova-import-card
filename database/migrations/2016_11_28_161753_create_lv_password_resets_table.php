<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvPasswordResetsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_password_resets', function (Blueprint $table) {
            $table->string('email')->index('password_resets_email_index');
            $table->string('token')->index('password_resets_token_index');
            $table->dateTime('created_at')->default('0000-00-00 00:00:00');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lv_password_resets');
    }
}
