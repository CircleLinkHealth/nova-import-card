<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationClosuresTable extends Migration
{
    public function up()
    {
        Schema::create('location_closure', function(Blueprint $table)
        {
            $table->increments('closure_id');

            $table->integer('ancestor', false, true);
            $table->integer('descendant', false, true);
            $table->integer('depth', false, true);

            $table->foreign('ancestor')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('descendant')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('location_closure', function(Blueprint $table)
        {
            Schema::dropIfExists('location_closure');
        });
    }
}
