<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveRnCanApproveCareplans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('cpm_settings', 'rn_can_approve_careplans')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->dropColumn('rn_can_approve_careplans');
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
        if (! Schema::hasColumn('cpm_settings', 'rn_can_approve_careplans')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->boolean('rn_can_approve_careplans')->default(0);
            });
        }
    }
}
