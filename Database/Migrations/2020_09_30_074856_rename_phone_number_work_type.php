<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\PhoneNumber;
use Illuminate\Database\Migrations\Migration;

class RenamePhoneNumberWorkType extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('phone_numbers')->where('type', '=', PhoneNumber::WORK)->update(
            [
                'type' => PhoneNumber::ALTERNATE,
            ]
        );
    }
}
