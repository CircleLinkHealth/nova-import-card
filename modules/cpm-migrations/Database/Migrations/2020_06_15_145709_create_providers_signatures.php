<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvidersSignatures extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providers_signatures');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers_signatures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('provider_info_id');
            $table->string('signature_src')->unique();
            $table->timestamps();

            $table->foreign('provider_info_id')
                ->references('id')
                ->on('provider_info')
                ->onDelete('cascade');
        });
    }
}
