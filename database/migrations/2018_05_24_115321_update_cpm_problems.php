<?php

use Illuminate\Database\Migrations\Migration;

class UpdateCpmProblems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('db:seed', [
            '--class' => 'UpdateBHIProblems',
        ]);

        Artisan::call('db:seed', [
            '--class' => 'CpmDefaultInstructionSeeder',
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
