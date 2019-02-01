<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitation_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('awv_patient_id');
            $table->unsignedInteger('survey_id');
            $table->string('link_token')->unique();
            $table->boolean('is_expired')->default(false);
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
        Schema::dropIfExists('invitation_links');
    }
}
