<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('q_a_import_summaries', function (Blueprint $table) {
            $table->boolean('has_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('q_a_import_summaries', function (Blueprint $table) {
            $table->dropColumn('has_phone');
        });
    }
}
