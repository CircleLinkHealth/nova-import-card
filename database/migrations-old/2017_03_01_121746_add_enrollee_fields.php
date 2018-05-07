<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnrolleeFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->string('zip')->after('address');
            $table->string('state')->after('address');
            $table->string('city')->after('address');
            $table->string('address_2')->after('address');

            $table->string('primary_insurance')->before('address');
            $table->string('secondary_insurance')->before('address');
            $table->string('cell_phone')->after('phone');
            $table->string('home_phone')->after('phone');
            $table->string('other_phone')->after('phone');
            $table->string('email')->before('address');
            $table->string('last_encounter')->before('address');
            $table->string('referring_provider_name')->before('address');
            $table->string('problems')->before('address');
            $table->string('ccm_condition_2')->after('problems');
            $table->string('ccm_condition_1')->after('problems');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            //
        });
    }
}
