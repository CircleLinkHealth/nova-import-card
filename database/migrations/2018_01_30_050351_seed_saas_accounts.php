<?php

use App\Practice;
use App\SaasAccount;
use App\User;
use Illuminate\Database\Migrations\Migration;

class SeedSaasAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $clh = SaasAccount::create([
            'name' => 'CircleLink Health',
        ]);

        Practice::withTrashed()
                ->where('id', '>', 0)
                ->update([
                    'saas_account_id' => $clh->id,
                ]);

        User::withTrashed()
            ->where('id', '>', 0)
            ->update([
                'saas_account_id' => $clh->id,
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
