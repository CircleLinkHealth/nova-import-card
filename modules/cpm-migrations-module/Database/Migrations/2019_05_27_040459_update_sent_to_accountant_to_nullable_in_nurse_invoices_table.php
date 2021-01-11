<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSentToAccountantToNullableInNurseInvoicesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('nurse_invoices', 'sent_to_accountant_at')) {
            Schema::table('nurse_invoices', function (Blueprint $table) {
                $table->dateTime('sent_to_accountant_at')->nullable(false)->change();
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasColumn('nurse_invoices', 'sent_to_accountant_at')) {
            Schema::table('nurse_invoices', function (Blueprint $table) {
                $table->dateTimeTz('sent_to_accountant_at')->nullable(true)->change();
            });
        }
    }
}
