<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientMonthlyBillingStatusesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_monthly_billing_statuses');

        if ( ! Schema::hasColumn('chargeable_patient_monthly_summaries', 'actor_id')) {
            Schema::table('chargeable_patient_monthly_summaries', function (Blueprint $table) {
                $table->unsignedInteger('actor_id')->nullable();

                $table->foreign('actor_id', 'cpms_actor_id_foreign')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_monthly_billing_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('patient_user_id');
            $table->date('chargeable_month');
            $table->unsignedInteger('actor_id')->nullable();
            $table->enum('status', ['approved', 'rejected', 'needs_qa'])->nullable()->default(null);

            $table->timestamps();

            $table->foreign('patient_user_id', 'pmbs_patient_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('actor_id', 'pmbs_actor_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        if (Schema::hasColumn('chargeable_patient_monthly_summaries', 'actor_id')) {
            Schema::table('chargeable_patient_monthly_summaries', function (Blueprint $table) {
                $table->dropForeign('cpms_actor_id_foreign');
                $table->dropColumn('actor_id');
            });
        }
    }
}
