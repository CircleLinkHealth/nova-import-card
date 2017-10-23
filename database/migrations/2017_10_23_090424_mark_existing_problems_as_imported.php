<?php

use Illuminate\Database\Migrations\Migration;

class MarkExistingProblemsAsImported extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('ccd_problems')
            ->update([
                'is_imported' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
