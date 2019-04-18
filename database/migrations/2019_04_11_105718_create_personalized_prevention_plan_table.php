<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonalizedPreventionPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personalized_prevention_plan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('display_name');
            $table->date('birth_date');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->unsignedInteger('user_id');
            $table->string('billing_provider');
            $table->json('hra_answers');
            $table->json('vitals_answers');
            $table->json('answers_for_eval');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personalized_prevention_plan');
    }
}
