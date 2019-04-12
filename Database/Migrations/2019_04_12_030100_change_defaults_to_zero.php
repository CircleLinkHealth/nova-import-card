<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultsToZero extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'nurse_monthly_summaries',
            function (Blueprint $table) {
                $table->integer('no_of_calls')
                      ->nullable(false)
                      ->default(0)
                      ->change();
                
                $table->integer('no_of_successful_calls')
                      ->default(0)
                      ->nullable(false)
                      ->change();
            }
        );
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
