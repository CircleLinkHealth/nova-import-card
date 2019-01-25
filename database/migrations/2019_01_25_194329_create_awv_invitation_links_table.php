<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAwvInvitationLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('awv_invitation_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_user_id');
            $table->string('patient_name');
            $table->date('birth_date');
            $table->unsignedInteger('survey_id');
            $table->string('link_token')->unique();
            $table->boolean('is_expired')->default(0);
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
        Schema::dropIfExists('awv_invitation_links');
    }
}
