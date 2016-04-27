<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCarePlanFields extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_care_plans', function (Blueprint $table) {
            $table->dropColumn('problems_list');
            $table->dropColumn('allergies_list');
            $table->dropColumn('medications_list');
            $table->dropColumn('track_care_transitions');
            $table->dropColumn('old_meds_list');
            $table->dropColumn('social_services');
            $table->dropColumn('appointments');
            $table->dropColumn('other');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_care_plans', function (Blueprint $table) {
            $table->text('problems_list');

            $table->text('allergies_list');

            $table->text('medications_list');

            $table->boolean('track_care_transitions')
                ->nullable();

            $table->text('old_meds_list')
                ->nullable();

            $table->text('social_services')
                ->nullable();

            $table->text('appointments')
                ->nullable();

            $table->text('other')
                ->nullable();
        });
    }

}
