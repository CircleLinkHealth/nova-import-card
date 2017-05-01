<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToPractices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('practices', function (Blueprint $table) {

            $table->text('invoice_recipients')->after('weekly_report_recipients');
            $table->text('bill_to_name')->after('invoice_recipients');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('practices', function (Blueprint $table) {

            $table->dropColumn('invoice_recipients');
            $table->dropColumn('bill_to_name');

        });

    }
}
