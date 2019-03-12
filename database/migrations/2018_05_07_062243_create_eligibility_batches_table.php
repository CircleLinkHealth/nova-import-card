<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEligibilityBatchesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('eligibility_batches');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('eligibility_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('initiator_id')->unsigned()->nullable()->index('eligibility_batches_initiator_id_foreign');
            $table->integer('practice_id')->unsigned()->nullable()->index('eligibility_batches_practice_id_foreign');
            $table->string('type');
            $table->integer('status')->nullable();
            $table->text('options')->nullable();
            $table->text('stats')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
