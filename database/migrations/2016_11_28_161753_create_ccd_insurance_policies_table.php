<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdInsurancePoliciesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccd_insurance_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccda_id')->unsigned()->index('ccd_insurance_policies_ccda_id_foreign');
            $table->integer('patient_id')->unsigned()->nullable()->index('ccd_insurance_policies_patient_id_foreign');
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('policy_id')->nullable();
            $table->string('relation')->nullable();
            $table->string('subscriber')->nullable();
            $table->boolean('approved');
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ccd_insurance_policies');
    }
}
