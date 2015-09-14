<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class WpUserProgramColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        // add program_id column to user
        Schema::connection('mysql_no_prefix')->table('wp_users', function($table)
        {
            $table->string('program_id', 10)->after('user_login');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
    }
}
