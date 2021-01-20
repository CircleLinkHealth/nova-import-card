<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeHumanFriendlyIndexesForBilling extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chargeable_patient_monthly_summaries', function (Blueprint $table) {
            $table->dropForeign('cpms_actor_id_foreign');
            $table->dropForeign('cpms_cs_id_foreign');
            $table->dropForeign('cpms_patient_user_id_foreign');

            $table->foreign('patient_user_id', 'c_p_m_s_patient_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('chargeable_service_id', 'c_p_m_s_c_s_id_foreign')
                ->references('id')
                ->on('chargeable_services')
                ->onDelete('set null');

            $table->foreign('actor_id', 'c_p_m_s_actor_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chargeable_patient_monthly_summaries', function (Blueprint $table) {
            $table->dropForeign('c_p_m_s_actor_id_foreign');
            $table->dropForeign('c_p_m_s_c_s_id_foreign');
            $table->dropForeign('c_p_m_s_patient_user_id_foreign');

            $table->foreign('chargeable_service_id', 'cpms_cs_id_foreign')
                ->references('id')
                ->on('chargeable_services')
                ->onDelete('cascade');

            $table->foreign('patient_user_id', 'cpms_patient_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('actor_id', 'cpms_actor_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
}
