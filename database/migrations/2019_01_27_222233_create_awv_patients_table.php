<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAwvPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('awv_patients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cpm_user_id');
            $table->date('birth_date');
            $table->string('number')->nullable();
            $table->timestamps();

            $table->foreign('cpm_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('awv_patients');
    }
}
