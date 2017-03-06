<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnrolleesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('enrollees',  function (Blueprint $table){

            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('provider_id')->nullable();
            $table->unsignedInteger('practice_id')->nullable();
            $table->unsignedInteger('mrn_number');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('phone');

            $table->text('invite_code');
            $table->string('status')->defaults('eligible');
            $table->unsignedInteger('attempt_count')->defaults(0);

            $table->dateTime('consented_at');
            $table->dateTime('last_attempt_at');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('provider_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('enrollees');
    }
}
