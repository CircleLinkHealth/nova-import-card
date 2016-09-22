<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveFromPrograms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wp_blogs', function (Blueprint $table) {

            $columns = [
                'short_display_name',
                'site_id',
                'path',
                'registered',
                'last_updated',
                'public',
                'archived',
                'mature',
                'spam',
                'deleted',
                'lang_id',
                'att_config',
            ];

            foreach ($columns as $col) {
                if (!Schema::hasColumn('wp_blogs', $col)) {
                    continue;
                }

                $table->dropColumn($col);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wp_blogs', function (Blueprint $table) {
            //
        });
    }
}
