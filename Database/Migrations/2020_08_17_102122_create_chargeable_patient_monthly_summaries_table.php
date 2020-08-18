<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargeablePatientMonthlySummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chargeable_patient_monthly_summaries');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chargeable_patient_monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('patient_user_id');
            $table->unsignedInteger('chargeable_service_id')->nullable();
            $table->date('chargeable_month');
            $table->unsignedInteger('actor_id')->nullable();
            $table->boolean('is_fulfilled')->default(0);
            $table->timestamps();

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
}
