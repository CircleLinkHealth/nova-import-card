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

                $table->unsignedInteger('inbound_enrollee_id')
                      ->nullable()
                      ->after('inbound_user_id');

                $table->foreign('inbound_enrollee_id')
                      ->references('id')
                      ->on('enrollees')
                      ->onUpdate('CASCADE')
                      ->onDelete('SET NULL');
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
                $table->dropForeign(['inbound_enrollee_id']);
                $table->dropColumn('inbound_enrollee_id');
            });
        }
    }
}
