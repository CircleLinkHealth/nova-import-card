<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChargeablesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chargeables', function (Blueprint $table) {
            $table->integer('chargeable_service_id')->unsigned();
            $table->integer('chargeable_id')->unsigned();
            $table->string('chargeable_type');
            $table->decimal('amount')->nullable();
            $table->timestamps();
            $table->unique(['chargeable_service_id','chargeable_id','chargeable_type'], 'cs_id_c_id_ct_id');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('chargeables');
    }
}
