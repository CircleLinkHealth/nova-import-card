<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->unsignedInteger('chargeable_service_id');
            $table->unsignedInteger('chargeable_id');
            $table->string('chargeable_type');
            $table->timestamps();

            $table->unique(['chargeable_service_id', 'chargeable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chargeables');
    }
}
