<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AddNurseCcmPlusNovaKeys extends Migration
{
    const NURSE_CCM_PLUS_ENABLED_FOR_ALL      = 'nurse_ccm_plus_enabled_for_all';
    const NURSE_CCM_PLUS_ENABLED_FOR_USER_IDS = 'nurse_ccm_plus_enabled_for_user_ids';
    const NURSE_CCM_PLUS_PAY_ALGO             = 'nurse_ccm_plus_pay_algo';

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
        DB::table('app_config')
            ->updateOrInsert(
                [
                    'config_key' => self::NURSE_CCM_PLUS_ENABLED_FOR_ALL,
                ],
                [
                    'config_value' => 'false',
                ]
            );

        DB::table('app_config')
            ->updateOrInsert(
                [
                    'config_key' => self::NURSE_CCM_PLUS_ENABLED_FOR_USER_IDS,
                ],
                [
                    'config_value' => '',
                ]
            );

        DB::table('app_config')
            ->updateOrInsert(
                [
                    'config_key' => self::NURSE_CCM_PLUS_PAY_ALGO,
                ],
                [
                    'config_value' => 'option_1',
                ]
            );
    }
}
