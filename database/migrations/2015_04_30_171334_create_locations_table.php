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

            $table->string('name');

            $table->string('phone');

            $table->string('address_line_1');
            $table->string('address_line_2');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');

            $table->string('billing_code');
            $table->string('location_code');

            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('locations')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('lv_locations', function(Blueprint $table)
        {
            Schema::dropIfExists('lv_locations');
        });
    }
}
