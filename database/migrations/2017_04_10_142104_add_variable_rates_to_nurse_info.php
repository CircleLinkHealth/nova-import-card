<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVariableRatesToNurseInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nurse_info', function (Blueprint $table) {

            $table->integer('high_rate')->default('30')->after('billing_type');
            $table->integer('low_rate')->default('10')->after('billing_type');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_info', function (Blueprint $table) {

            $table->dropColumn('high_rate');
            $table->dropColumn('low_rate');

        });
    }
}
