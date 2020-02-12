<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePcmProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcm_problems', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->unsignedInteger('practice_id');
            
            $table->string('code_type');
            $table->string('code');
            $table->string('description');
            
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('cascade');
            
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
        Schema::dropIfExists('pcm_problems');
    }
}
