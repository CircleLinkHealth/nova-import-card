<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnrolleeFamilyMembersTable extends Migration
{
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
        Schema::dropIfExists('enrollee_family_members');
    }
}
