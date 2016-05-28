<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeBiometrics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_biometrics', function (Blueprint $table) {
            $table->unsignedInteger('type')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET foreign_key_checks = 0');

        Schema::table('cpm_biometrics', function (Blueprint $table) {
            $table->string('type')->change();
        });
        
        DB::statement('SET foreign_key_checks = 1');

    }
}
