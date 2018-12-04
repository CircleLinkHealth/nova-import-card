<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('practice_id')->unsigned()->default(4)->index('locations_practice_id_foreign');
            $table->boolean('is_primary')->default(0);
            $table->string('external_department_id')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('fax')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable()->default('');
            $table->string('city');
            $table->string('state')->nullable();
            $table->text('timezone', 65535)->nullable();
            $table->string('postal_code');
            $table->string('ehr_login')->nullable();
            $table->string('ehr_password')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('locations');
    }
}
