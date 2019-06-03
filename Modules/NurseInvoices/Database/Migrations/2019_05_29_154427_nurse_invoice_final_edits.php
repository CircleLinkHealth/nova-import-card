<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\AppConfig;
use CircleLinkHealth\NurseInvoices\Helpers\NurseInvoiceDisputeDeadline;
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
        if ( ! app()->environment('testing')) {
            //throws exceptions it table does not exist
            //is a problem for CI
            //config is cached, so I chose this instead of checking if table exists
            AppConfig::updateOrCreate([
                'config_key' => NurseInvoiceDisputeDeadline::NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_KEY,
            ], [
                'config_value' => NurseInvoiceDisputeDeadline::DEFAULT_NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_DAY_AND_TIME,
            ]);
        }

        Schema::table('nurse_invoices', function (Blueprint $table) {
            $table->dropColumn('sent_to_accountant_at');
        });

        Schema::table('nurse_invoices', function (Blueprint $table) {
            $table->timestamp('sent_to_accountant_at')->nullable()->after('month_year');
        });
    }
}
