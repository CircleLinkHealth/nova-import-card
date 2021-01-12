\
<?php

// This file is part of CarePlan Manager by CircleLink Health.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class L58MakeNullable extends Migration
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
        config(['database.connections.mysql.strict' => false]);
        DB::reconnect();
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $tableObj) {
            $tableName = reset($tableObj);
            if (Schema::hasColumn($tableName, 'created_at')) {
                try {
                    DB::statement("ALTER TABLE $tableName CHANGE created_at created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
                } catch (Exception $e) {
                    //sometimes it will fail if column type is not timestamp
                }
            }

            if (Schema::hasColumn($tableName, 'updated_at')) {
                try {
                    DB::statement("ALTER TABLE $tableName CHANGE updated_at updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
                } catch (Exception $e) {
                    //sometimes it will fail if column type is not timestamp
                }
            }
        }

        DB::statement('ALTER TABLE notes CHANGE performed_at performed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE lv_observations CHANGE obs_date obs_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE lv_observations CHANGE obs_date_gmt obs_date_gmt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE lv_comments CHANGE comment_date comment_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE lv_comments CHANGE comment_date_gmt comment_date_gmt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE lv_page_timer CHANGE start_time start_time TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE lv_page_timer CHANGE end_time end_time TIMESTAMP NULL DEFAULT NULL');
        Schema::table('lv_page_timer', function (Blueprint $table) {
            $table->string('query_string')->nullable(true)->default(null)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->nullable(true)->default(null)->change();
        });

        Schema::table('calls', function (Blueprint $table) {
            $table->text('inbound_phone_number')->nullable(true)->default(null)->change();
            $table->text('window_start')->nullable(true)->default(null)->change();
            $table->text('window_end')->nullable(true)->default(null)->change();
        });

        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->text('approved')->nullable(true)->default(null)->change();
        });

        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->text('approved')->nullable(true)->default(null)->change();
        });

        Schema::table('lv_comments', function (Blueprint $table) {
            $table->integer('comment_karma')->unsigned()->nullable(true)->default(null)->change();
        });
    }
}
