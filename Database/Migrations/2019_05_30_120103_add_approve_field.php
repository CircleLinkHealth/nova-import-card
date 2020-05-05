<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApproveField extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('nurse_invoices', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nurse_invoices', function (Blueprint $table) {
            $table->timestamp('nurse_approved_at')->nullable()->after('month_year');
            $table->boolean('is_nurse_approved')->nullable()->after('month_year');
        });
    }
}
