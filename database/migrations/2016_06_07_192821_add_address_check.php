<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddressCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('q_a_import_summaries', function (Blueprint $table) {
            $table->boolean('has_street_address');
            $table->boolean('has_zip');
            $table->boolean('has_city');
            $table->boolean('has_state');
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
            $table->dropColumn('has_street_address');
            $table->dropColumn('has_zip');
            $table->dropColumn('has_city');
            $table->dropColumn('has_state');
        });
    }
}
