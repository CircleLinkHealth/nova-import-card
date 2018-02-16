<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEhrKeychainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ehr_keychains', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ehr_id');
            $table->timestamps();

            $table->foreign();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ehr_keychains');
    }
}
