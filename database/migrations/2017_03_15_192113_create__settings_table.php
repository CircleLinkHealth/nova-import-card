<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('settingsable_id');
            $table->string('settingsable_type');
            $table->boolean('auto_approve_careplans')->default(false);
            $table->boolean('dm_pdf_careplan')->default(true);
            $table->boolean('dm_pdf_notes')->default(true);
            $table->boolean('email_careplan_approval_reminders')->default(true);
            $table->boolean('email_note_was_forwarded')->default(true);
            $table->boolean('efax_pdf_careplan')->default(true);
            $table->boolean('efax_pdf_notes')->default(true);
            $table->timestamps();

            $table->unique([
                'settingsable_id',
                'settingsable_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
