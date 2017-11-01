<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('notes_patient_id_foreign');
            $table->integer('author_id')->unsigned()->index('notes_author_id_foreign');
            $table->text('body', 65535);
            $table->boolean('isTCM');
            $table->timestamps();
            $table->text('type', 65535);
            $table->dateTime('performed_at')->default('0000-00-00 00:00:00');
            $table->integer('logger_id')->unsigned()->nullable()->index('notes_logger_id_foreign');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('notes');
    }
}
