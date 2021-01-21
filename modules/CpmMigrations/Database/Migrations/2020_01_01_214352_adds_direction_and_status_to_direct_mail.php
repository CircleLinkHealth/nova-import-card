<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsDirectionAndStatusToDirectMail extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tblName = 'direct_mail_messages';

        Schema::table($tblName, function (Blueprint $table) {
            $table->dropColumn('direction');
            $table->dropColumn('error_text');
            $table->dropColumn('status');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tblName = 'direct_mail_messages';

        if ( ! Schema::hasColumn($tblName, 'direction')) {
            Schema::table($tblName, function (Blueprint $table) {
                $table->string('error_text')->nullable()->after('id');
                $table->enum('status', ['success', 'fail'])->after('id');
                $table->enum('direction', ['sent', 'received'])->after('id');
            });

            DB::table($tblName)->update(['direction' => 'received', 'status' => 'success']);
        }
    }
}