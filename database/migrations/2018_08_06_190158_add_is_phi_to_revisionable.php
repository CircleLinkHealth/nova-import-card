<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPhiToRevisionable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revisions', function (Blueprint $table) {
            if (! Schema::hasColumns('revisions', ['is_phi'])) {
                $table->boolean('is_phi')
                      ->default(false)
                      ->after('ip');
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
        Schema::table('revisions', function (Blueprint $table) {
            $table->dropColumn('is_phi');
        });
    }
}
