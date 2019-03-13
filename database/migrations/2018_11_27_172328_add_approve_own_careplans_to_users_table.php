<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApproveOwnCareplansToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_info', function (Blueprint $table) {
            $table->boolean('approve_own_care_plans')->after('specialty')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_info', function (Blueprint $table) {
            $table->dropColumn('approve_own_care_plans');
        });
    }
}
