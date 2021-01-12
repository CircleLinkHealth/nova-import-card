<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class MakeJsonFieldToJson extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE ccdas SET json = NULL WHERE json = ''");
        DB::statement('ALTER TABLE ccdas CHANGE COLUMN json json json NULL');
    }
}
