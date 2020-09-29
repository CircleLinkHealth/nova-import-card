<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargeableLocationMonthlySummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chargeable_location_monthly_summaries');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chargeable_location_monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('chargeable_service_id');
            $table->date('chargeable_month');
            $table->decimal('amount')->nullable();
            $table->boolean('is_locked')->default(0);
            $table->timestamps();

            $table->foreign('location_id', 'c_l_m_s_location_id_foreign')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');

            $table->foreign('chargeable_service_id', 'c_l_m_s_chargeable_service_id_foreign')
                ->references('id')
                ->on('chargeable_services')
                ->onDelete('cascade');
        });
    }
}
