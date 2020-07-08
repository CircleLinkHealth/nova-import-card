<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSuccessfulClinicalCallToNotesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex(['successful_clinical_call']);
            $table->dropColumn(['successful_clinical_call']);
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->boolean('successful_clinical_call')
                ->index()
                ->nullable(false)
                ->default(false);
        });

        DB::statement('
update notes
set successful_clinical_call = 1
where exists(select id from calls where calls.note_id = notes.id);');
    }
}
