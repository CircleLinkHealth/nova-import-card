<?php

use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use Illuminate\Database\Migrations\Migration;

class IgnoreDemoAndTestdrive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DocumentLog::where('practice_id', '=', 8)
            ->where('practice_id', '=', 9)
            ->delete();

        ProviderLog::where('practice_id', '=', 8)
            ->where('practice_id', '=', 9)
            ->delete();
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
