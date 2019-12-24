<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaxesTable extends Migration
{
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fax_logs');
    }
}
