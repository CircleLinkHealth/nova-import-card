<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerNotificationContactTimePreferencesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_notification_contact_time_preferences');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_notification_contact_time_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('contactable_type');
            $table->unsignedInteger('contactable_id');
            $table->string('notification');
            $table->string('day');
            $table->string('from');
            $table->string('to');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('max_per_hour')->nullable();
            $table->timestamps();
        });
    }
}
