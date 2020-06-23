<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Console\Commands\UpdateToledoProviderFromExcelCommand;
use Illuminate\Database\Migrations\Migration;

class AddToledoProvidersNpiNumber extends Migration
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
        if (isCpm()) {
            \Illuminate\Support\Facades\Artisan::call(UpdateToledoProviderFromExcelCommand::class);
        }
    }
}
