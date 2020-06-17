<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceInTwilioCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('twilio_calls', 'source')) {
            Schema::table('twilio_calls', function (Blueprint $table) {
                $table->string('source')
                      ->nullable()
                      ->after('id')
                      ->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('twilio_calls', 'source')) {
            Schema::table('twilio_calls', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
}
