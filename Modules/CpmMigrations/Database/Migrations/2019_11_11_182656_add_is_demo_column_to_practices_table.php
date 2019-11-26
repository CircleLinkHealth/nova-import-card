<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDemoColumnToPracticesTable extends Migration
{
    protected $demoPracticeNames = [
        'none',
        'demo',
        'testdrive',
        'mdally-demo',
        'nektarios-test-practice',
    ];

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->boolean('is_demo')->default(0)->after('active');
        });

        Practice::whereIn('name', $this->demoPracticeNames)
            ->update(['is_demo' => 1]);
    }
}
