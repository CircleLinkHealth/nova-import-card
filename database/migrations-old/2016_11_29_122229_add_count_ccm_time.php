<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountCcmTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('count_ccm_time')
                ->after('id')
                ->default(false);
        });

        $users = User::ofType([
            'provider',
            'care-center',
            'med_assistant',
        ])
            ->get();

        foreach ($users as $u) {
            $u->count_ccm_time = true;
            $u->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('count_ccm_time');
        });
    }
}
