<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnrollmentInvitationLetters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollment_invitation_letters', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('practice_id');
            $table->string('practice_logo_src');
            $table->string('customer_signature_src');
            $table->string('signatory_name');
            $table->json('letter');
            $table->timestamps();

//            $table->foreign('practice_id')
//                ->references('id')
//                ->on('practices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
