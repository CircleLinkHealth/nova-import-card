<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyPppDbDataTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {

    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasColumns('ppp_task_recommendations', ['codes', 'rec_task_titles'])) {
            Schema::table('ppp_task_recommendations', function (Blueprint $table) {
                $table->dropColumn('codes', 'rec_task_titles');
            });
        }

        TaskRecommendationsSeeder::run();
    }
}

