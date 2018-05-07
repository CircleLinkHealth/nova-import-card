<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckboxesToPractice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->boolean('send_alerts')
                ->after('same_clinical_contact')
                ->default(true);

            $table->boolean('auto_approve_careplans')
                ->after('same_clinical_contact')
                ->default(false);
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
            $table->dropColumn('send_alerts');
            $table->dropColumn('auto_approve_careplans');
        });
    }
}
