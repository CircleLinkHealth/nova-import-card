<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDefaultNoteSize extends Migration
{
    public function __construct()
    {
        //This fixes the error that doctrine throws when renaming a table that contains an enum field.
        //https://stackoverflow.com/questions/33140860/laravel-5-1-unknown-database-type-enum-requested
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            $table->decimal('note_font_size')
                ->default(0.8)
                ->change();
        });
    }
}
