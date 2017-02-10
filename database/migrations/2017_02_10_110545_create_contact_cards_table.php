<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_cards', function (Blueprint $table) {
            $table->increments('id');

            $table->string('contactcardable_type');
            $table->unsignedInteger('contactcardable_id');

            $table->string('email');
            $table->string('emr_direct');
            $table->string('work_phone');
            $table->string('cell_phone');
            $table->string('home_phone');

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
        Schema::dropIfExists('contact_cards');
    }
}
