<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AwvVitalsQuestion5AllowHtml extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = 'question_groups';

        DB::table($table)
            ->where(
                'body',
                'Based off of the <a href="http://mini-cog.com/wp-content/uploads/2015/12/Universal-Mini-Cog-Form-011916.pdf">Mini-Cog(c) assessment</a>, how did your patient score?'
            )
            ->update(['body' => 'Based off of the Mini-Cog(c) assessment, how did your patient score? http://mini-cog.com/wp-content/uploads/2015/12/Universal-Mini-Cog-Form-011916.pdf']);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'question_groups';

        DB::table($table)
            ->where(
                'body',
                'Based off of the Mini-Cog(c) assessment, how did your patient score? http://mini-cog.com/wp-content/uploads/2015/12/Universal-Mini-Cog-Form-011916.pdf'
            )
            ->update(['body' => 'Based off of the <a target="_blank" href="http://mini-cog.com/wp-content/uploads/2015/12/Universal-Mini-Cog-Form-011916.pdf">Mini-Cog(c) assessment</a>, how did your patient score?']);
    }
}
