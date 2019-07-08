<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_instances', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('survey_id');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

           /* $table->foreign('survey_id')
                  ->references('id')->on('surveys')
                  ->onDelete('cascade');*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('survey_instances');
    }
}
