<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrolleeFamilyMembersTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enrollee_family_members');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollee_family_members', function (Blueprint $table) {
            $table->unsignedInteger('enrollee_id');
            $table->unsignedInteger('family_member_id');

            $table->foreign('enrollee_id')
                ->references('id')
                ->on('enrollees')
                ->onDelete('cascade');

            $table->foreign('family_member_id')
                ->references('id')
                ->on('enrollees')
                ->onDelete('cascade');
        });
    }
}
