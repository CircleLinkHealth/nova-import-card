<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSameContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->boolean('same_clinical_contact')
                ->nullable()
                ->after('federal_tax_id');

            $table->boolean('same_ehr_login')
                ->nullable()
                ->after('federal_tax_id');
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
            $table->dropColumn('same_clinical_contact');
            $table->dropColumn('same_ehr_login');
        });
    }
}
