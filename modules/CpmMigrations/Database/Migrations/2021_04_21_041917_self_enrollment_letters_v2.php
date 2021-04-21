<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SelfEnrollmentLettersV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('self_enrollment_letters_v2')) {
            Schema::create('self_enrollment_letters_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('practice_id');
                $table->longText('body');
                $table->json('options');
                $table->boolean('is_active');
                $table->timestamps();
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
        Schema::dropIfExists('self_enrollment_letters_v2');
    }
}
