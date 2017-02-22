<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->string('external_id')
                ->nullable()
                ->after('same_clinical_contact');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->string('external_department_id')
                ->nullable()
                ->after('practice_id');
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
            $table->dropColumn('external_id');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('external_department_id');
        });
    }
}
