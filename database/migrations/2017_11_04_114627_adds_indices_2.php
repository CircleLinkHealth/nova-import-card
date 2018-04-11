<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsIndices2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->index(['patient_id']);
        });

        Schema::table('lv_activities', function (Blueprint $table) {
            $table->index(['patient_id', 'logged_from', 'provider_id', 'performed_at', 'type'], 'pat_lgFrm_prov_perfAt_type');
        });

        Schema::table('cpm_mail_logs', function (Blueprint $table) {
            $table->index(['receiver_cpm_id']);
            $table->index(['sender_cpm_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex(['patient_id']);
        });

        Schema::table('lv_activities', function (Blueprint $table) {
            $table->dropIndex('pat_lgFrm_prov_perfAt_type');
        });

        Schema::table('cpm_mail_logs', function (Blueprint $table) {
            $table->dropIndex(['receiver_cpm_id']);
            $table->dropIndex(['sender_cpm_id']);
        });
    }
}
