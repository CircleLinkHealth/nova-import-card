<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPracticeAndLocationToCcda extends Migration
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
                $table->unsignedInteger('practice_id')
                    ->nullable()
                    ->after('id');

                $table->foreign('practice_id')
                    ->references('id')
                    ->on('practices')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->unsignedInteger('location_id')
                    ->nullable()
                    ->after('id');

                $table->foreign('location_id')
                    ->references('id')
                    ->on('locations')
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
