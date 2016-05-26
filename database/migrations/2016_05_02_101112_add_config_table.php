<?php

use App\AppConfig;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('app_config')) {
            Schema::drop('app_config');
        }
        if (!Schema::hasTable('app_config')) {
            echo 'Schema::add app_config table'.PHP_EOL;
            Schema::create('app_config', function (Blueprint $table) {
                $table->increments('id');
                $table->string('config_key');
                $table->string('config_value');
                $table->timestamps();
            });

            // add cur_month_ccm_time_last_reset
            $config = new AppConfig;
            $config->config_key = 'cur_month_ccm_time_last_reset';
            $config->config_value = '';
            $config->save();
            echo 'added cur_month_ccm_time_last_reset config_key'.PHP_EOL;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('app_config')) {
            Schema::drop('app_config');
        }
    }

}
