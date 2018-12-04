<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChargeableServicesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chargeable_services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->text('description', 65535)->nullable();
            $table->decimal('amount')->nullable();
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
        Schema::drop('chargeable_services');
    }
}
