<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrollmentInvitationsBatches extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enrollment_invitations_batches');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollment_invitations_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('practice_id')->unsigned()->nullable()->index('eligibility_batches_practice_id_foreign');
            $table->string('type');
            $table->timestamps();
            
            $table->foreign('practice_id')->references('id')->on('practices')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
