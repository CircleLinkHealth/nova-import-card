<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Call;

class AddIsManualToCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->boolean('is_manual')
                  ->after('scheduler')
                  ->default(false)
                  ->nullable();
        });

        Call::with('schedulerUser')->chunk(200, function ($records) {
            foreach ($records as $record) {
                if ($record->isFromCareCenter) {
                    $record->is_manual = true;
                    $record->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('is_manual');
        });
    }
}
