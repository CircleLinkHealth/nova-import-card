<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class NurseInvoiceFinalEdits extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nurse_invoices', function (Blueprint $table) {
            $table->dropColumn('sent_to_accountant_at');
        });

        Schema::table('nurse_invoices', function (Blueprint $table) {
            $table->timestamp('sent_to_accountant_at')->nullable()->after('month_year');
        });
    }
}
