<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoSoftRejectedInCareAmbassadorLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {
            $table->integer('no_soft_rejected')
                ->default(0)
                ->after('no_rejected');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        if (Schema::hasColumn('care_ambassador_logs', 'no_soft_rejected')) {
            Schema::table('care_ambassador_logs', function (Blueprint $table) {
                $table->dropColumn('no_soft_rejected');
            });
        }
    }
}
