<?php

use Illuminate\Database\Seeder;

class AppConfigTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('app_config')->delete();

        \DB::table('app_config')->insert($this->rows());
    }

    public function rows(): array
    {
        return array(
            0 =>
            array(
                'id' => 1,
                'config_key' => 'cur_month_ccm_time_last_reset',
                'config_value' => '2017-04-01 00:01:08',
                'created_at' => '2016-05-27 23:42:50',
                'updated_at' => '2017-04-01 00:01:08',
            ),
            1 =>
            array(
                'id' => 2,
                'config_key' => 'admin_stylesheet',
                'config_value' => 'admin-bootswatch-yeti.css',
                'created_at' => '2016-06-08 20:44:00',
                'updated_at' => '2016-07-05 12:25:42',
            ),
            2 =>
            array(
                'id' => 3,
                'config_key' => 'billing_invoice_count',
                'config_value' => '1205',
                'created_at' => '2017-04-25 17:52:30',
                'updated_at' => '2017-12-19 08:52:39',
            ),
            3 =>
            array(
                'id' => 4,
                'config_key' => 'default_care_plan_template_id',
                'config_value' => '1',
                'created_at' => '2017-06-09 09:03:20',
                'updated_at' => '2017-06-09 09:20:20',
            )
        );
    }
}
