<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFullTextIndicesOnEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE enrollees DROP INDEX address');
        DB::statement('ALTER TABLE enrollees DROP INDEX phone');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE FULLTEXT INDEX address ON enrollees (address, address_2)');
        //ommitting primary phone, different format
        DB::statement('CREATE FULLTEXT INDEX phone ON enrollees (other_phone, home_phone, cell_phone)');
    }
}
