<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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

            $table->unsignedInteger('ccda_id');
            $table->foreign('ccda_id')
                ->references('id')
                ->on((new \App\Models\CCD\Ccda())->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedInteger('patient_id')->nullable();
            $table->foreign('patient_id')
                ->references('id')
                ->on((new \App\User())->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('name')->nullable();
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
