<?php

use App\Importer\Models\ItemLogs\DocumentLog;
use Illuminate\Database\Migrations\Migration;

class IgnoreAthenahealthCustodian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DocumentLog::where('custodian', '=', 'athenahealth')
            ->update([
                'ml_ignore' => true,
            ]);

        DocumentLog::where('custodian', '=', 'Novant Health')
            ->update([
                'ml_ignore' => true,
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
