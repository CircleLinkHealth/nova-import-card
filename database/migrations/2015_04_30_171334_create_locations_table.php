<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('locations', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('position', false, true);
            $table->integer('real_depth', false, true);
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('locations')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('locations', function(Blueprint $table)
        {
            Schema::dropIfExists('locations');
        });
    }
}
