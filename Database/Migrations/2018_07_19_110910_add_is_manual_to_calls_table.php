<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsManualToCallsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('is_manual');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->boolean('is_manual')
                ->after('scheduler')
                ->default(false)
                ->nullable();
        });

        if (class_exists('CircleLinkHealth\SharedModels\Entities\Call', false)) {
            \CircleLinkHealth\SharedModels\Entities\Call::with('schedulerUser')->chunk(200, function ($records) {
                foreach ($records as $record) {
                    if ($record->isFromCareCenter) {
                        $record->is_manual = true;
                        $record->save();
                    }
                }
            });
        }
    }
}
