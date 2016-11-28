<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('appointments')) ; //check whether users table has email column
        {

            Schema::create('appointments', function (Blueprint $table) {

                $table->increments('id');

                $table->unsignedInteger('patient_id');
                $table->unsignedInteger('author_id');
                $table->unsignedInteger('provider_id')->nullable();

                $table->date('date');
                $table->time('time');

                $table->text('status');

                $table->text('comment');

                $table->foreign('patient_id')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('CASCADE')
                    ->onDelete('CASCADE');

                $table->foreign('author_id')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('CASCADE')
                    ->onDelete('CASCADE');

                $table->foreign('provider_id')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('CASCADE')
                    ->onDelete('CASCADE');

            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {

            $table->drop();
            
        });
    }
}
