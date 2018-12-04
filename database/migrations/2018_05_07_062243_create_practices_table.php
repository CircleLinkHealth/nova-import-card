<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePracticesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('practices');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('practices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('saas_account_id')->unsigned()->nullable()->index('practices_saas_account_id_foreign');
            $table->integer('ehr_id')->unsigned()->nullable()->index('practices_ehr_id_foreign');
            $table->integer('user_id')->unsigned()->nullable()->index('wp_blogs_user_id_foreign');
            $table->string('name', 100)->unique('wp_blogs_name_unique');
            $table->string('display_name')->nullable();
            $table->boolean('active')->default(0);
            $table->float('clh_pppm', 10, 0)->nullable();
            $table->integer('term_days')->default(30);
            $table->string('federal_tax_id')->nullable();
            $table->boolean('same_ehr_login')->nullable();
            $table->boolean('same_clinical_contact')->nullable();
            $table->boolean('auto_approve_careplans')->default(0);
            $table->boolean('send_alerts')->default(1);
            $table->text('weekly_report_recipients')->nullable();
            $table->text('invoice_recipients', 65535)->nullable();
            $table->text('bill_to_name', 65535)->nullable();
            $table->string('external_id')->nullable();
            $table->string('outgoing_phone_number')->default('+18886958537');
            $table->timestamps();
            $table->softDeletes();
            $table->string('sms_marketing_number')->nullable();
        });
    }
}
