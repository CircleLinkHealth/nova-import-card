<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValidationChecks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imported_medical_records', function (Blueprint $table) {
            $table->json('validation_checks')
                ->after('duplicate_id')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imported_medical_records', function (Blueprint $table) {
            $table->dropColumn('validation_checks');
        });
    }
}
