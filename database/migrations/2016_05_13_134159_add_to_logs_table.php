<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_demographics_logs', function (Blueprint $table) {
            $table->renameColumn('language', 'preferred_contact_language');
            $table->string('consent_date');
            $table->string('preferred_contact_timezone');
            $table->string('study_phone_number');
            $table->unsignedInteger('location_id');
//not sure why this is failing come back later
//            $table->foreign('location_id')
//                ->references('id')
//                ->on((new \App\Location())->getTable())
//                ->onUpdate('cascade')
//                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('set foreign_key_checks = 0');
        Schema::table('ccd_demographics_logs', function (Blueprint $table) {
            $table->renameColumn('preferred_contact_language', 'language');
            $table->dropColumn('consent_date');
            $table->dropColumn('preferred_contact_timezone');
            $table->dropColumn('study_phone_number');
            $table->dropColumn('location_id');
        });
        DB::statement('set foreign_key_checks = 1');

    }
}
