<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFullTextSearchToPracticePullDemsAndSupplementalData extends Migration
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
        DB::statement('ALTER TABLE supplemental_patient_data ADD FULLTEXT full(first_name, last_name)');
        DB::statement('ALTER TABLE practice_pull_demographics ADD FULLTEXT full(first_name, last_name)');
    }
}
