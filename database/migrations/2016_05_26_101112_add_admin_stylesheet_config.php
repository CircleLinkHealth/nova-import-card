<?php

use App\AppConfig;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminStylesheetConfig extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('app_config')) {
            echo 'Schema::add admin_stylesheet to app_config table'.PHP_EOL;

            $appConfigs = AppConfig::all();
            $adminStylesheet = $appConfigs->where('config_key', 'admin_stylesheet')->first();
            if(!$adminStylesheet) {
                // add admin_stylesheet
                $config = new AppConfig;
                $config->config_key = 'admin_stylesheet';
                $config->config_value = 'admin-bootswatch-default.css';
                $config->save();
                echo 'added admin_stylesheet config_key'.PHP_EOL;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

}
