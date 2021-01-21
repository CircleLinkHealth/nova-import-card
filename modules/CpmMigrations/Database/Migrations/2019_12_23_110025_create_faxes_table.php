<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaxesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fax_logs');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('fax_logs');

        Schema::create('fax_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('vendor')->default('phaxio');
            $table->unsignedInteger('fax_id');
            $table->string('status')->nullable();
            $table->enum('direction', ['sent', 'received']);
            $table->json('response');

            $table->timestamps();
        });
    }
}
