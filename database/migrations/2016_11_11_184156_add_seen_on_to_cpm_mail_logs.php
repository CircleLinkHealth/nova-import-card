<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSeenOnToCpmMailLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_mail_logs', function (Blueprint $table) {

            if(!Schema::hasColumn('cpm_mail_logs', 'seen_on')) ; //check whether users table has email column
            {
                $table->dateTime('seen_on')->nullable();
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
        Schema::table('cpm_mail_logs', function (Blueprint $table) {

            if(Schema::hasColumn('cpm_mail_logs', 'seen_on')) ; //check whether users table has email column
            {
                $table->dropColumn('seen_on');
            }

        });
    }
}
