<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('notifiable_id');
            $table->string('notifiable_type');
            $table->boolean('dm_pdf_careplan');
            $table->boolean('dm_pdf_notes');
            $table->boolean('email_careplan_approval_reminders');
            $table->boolean('email_note_was_forwarded');
            $table->boolean('efax_pdf_careplan');
            $table->boolean('efax_pdf_notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_setings');
    }
}
