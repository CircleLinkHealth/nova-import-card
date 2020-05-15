<?php

use CircleLinkHealth\Eligibility\Database\Seeders\EnrollmentInvitationLetterSeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EnrollmentSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! isUnitTestingEnv()) {
            Artisan::call('db:seed', [
                '--class' => EnrollmentInvitationLetterSeeder::class,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
