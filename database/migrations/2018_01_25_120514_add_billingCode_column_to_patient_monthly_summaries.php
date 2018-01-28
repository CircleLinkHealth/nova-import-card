<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingCodeColumnToPatientMonthlySummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->unsignedInteger('billing_code')
                  ->default(1)
                  ->after('actor_id');

            $table->foreign('billing_code')
                  ->references('id')->on('chargeable_services')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->dropForeign(['billing_code']);
            $table->dropColumn('billing_code');
        });
    }
}
