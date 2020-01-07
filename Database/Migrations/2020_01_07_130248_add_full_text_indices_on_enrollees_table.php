<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFullTextIndicesOnEnrolleesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE FULLTEXT INDEX address ON enrollees (address, address_2)');
        DB::statement('CREATE FULLTEXT INDEX phone ON enrollees (other_phone, home_phone, cell_phone)');
    }

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
}
