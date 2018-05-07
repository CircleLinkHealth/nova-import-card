<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniquePair extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('practice_user', function (Blueprint $table) {
                $table->unique([
                    'program_id',
                    'user_id',
                ]);
            });
        } catch (\Exception $e) {
            //already ran this on prod, so it doesn't matter if it doesn't run elsewhere
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('practice_user', function (Blueprint $table) {
            //
        });
    }
}
