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
            $table->integer('patient_id')->unsigned()->index();
            $table->integer('author_id')->unsigned()->index('notes_author_id_foreign');
            $table->text('body', 65535)->nullable();
            $table->boolean('isTCM')->default(0);
            $table->boolean('did_medication_recon')->default(0);
            $table->timestamps();
            $table->text('type', 65535)->nullable();
            $table->dateTime('performed_at')->nullable();
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
