<?php

use App\Importer\Models\ItemLogs\ProviderLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MlIgnoreAthenasAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ProviderLog::where('city', '=', 'Watertown')
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
        Schema::table('ccd_provider_logs', function (Blueprint $table) {
            //
        });
    }
}
