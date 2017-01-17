<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingProviderIdToCcda extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'ccdas',
            'ccd_document_logs',
            'ccd_provider_logs',
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->unsignedInteger('billing_provider_id')
                    ->nullable()
                    ->after('practice_id');

                $table->foreign('billing_provider_id')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            //
        });
    }
}
