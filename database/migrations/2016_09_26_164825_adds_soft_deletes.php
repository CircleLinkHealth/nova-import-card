<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddsSoftDeletes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nurse_contact_window', function (Blueprint $table) {
            $table->date('date')->after('nurse_info_id');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_contact_window', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropSoftDeletes();
        });
    }
}
