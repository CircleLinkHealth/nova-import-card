<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\DirectMailMessage;
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
        $tblName = (new DirectMailMessage())->getTable();

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
        $tblName = (new DirectMailMessage())->getTable();

        if ( ! Schema::hasColumn($tblName, 'direction')) {
            Schema::table($tblName, function (Blueprint $table) {
                $table->string('error_text')->nullable()->after('id');
                $table->enum('status', [DirectMailMessage::STATUS_SUCCESS, DirectMailMessage::STATUS_FAIL])->after('id');
                $table->enum('direction', [DirectMailMessage::DIRECTION_SENT, DirectMailMessage::DIRECTION_RECEIVED])->after('id');
            });

            DB::table($tblName)->update(['direction' => 'received', 'status' => 'success']);
        }
    }
}
