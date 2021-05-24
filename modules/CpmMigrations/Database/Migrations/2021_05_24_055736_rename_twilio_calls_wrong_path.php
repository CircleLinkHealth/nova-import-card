<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTwilioCallsWrongPath extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \CircleLinkHealth\SharedModels\Entities\VoiceCall::where('voice_callable_type', 'App\TwilioCall')
            ->update([
                'voice_callable_type' => 'CircleLinkHealth\TwilioIntegration\Models\TwilioCall'
            ]);
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
