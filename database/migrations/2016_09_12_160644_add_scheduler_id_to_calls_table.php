<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSchedulerIdToCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('calls', function (Blueprint $table) {

            if (!Schema::hasColumn('calls', 'scheduler')) {

                $table->text('scheduler')->nullable();

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

            if (Schema::hasColumn('calls', 'scheduler')) {

                $table->dropColumn('scheduler');

            }


        });

    }
}
