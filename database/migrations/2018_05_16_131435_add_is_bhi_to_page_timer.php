<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsBhiToPageTimer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lv_page_timer', function(Blueprint $table)
		{
			$table->boolean('is_behavioral')->after('provider_id')->nullable();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lv_page_timer', function(Blueprint $table)
		{
			$table->dropColumn('is_behavioral');
		});
    }
}
