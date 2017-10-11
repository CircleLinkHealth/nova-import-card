<?php

use App\ProviderInfo;
use Illuminate\Database\Migrations\Migration;

class MigrateQualificationToSuffix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $providers = ProviderInfo::withTrashed()
            ->whereNotNull('qualification')
            ->where('qualification', '!=', '')
            ->get();

        foreach ($providers as $p) {
            if (!$p->user) {
                continue;
            }

            $p->user->suffix = $p->qualification;
            $p->user->save();
        }
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
