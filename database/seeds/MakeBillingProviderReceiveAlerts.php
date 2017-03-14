<?php

use App\User;
use Illuminate\Database\Seeder;

class MakeBillingProviderReceiveAlerts extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::ofType('participant')->get() as $user) {
            if (!$user->primaryPractice) {
                continue;
            }

            if (in_array($user->primaryPractice->id, [
                120,
                134,
            ])) {
                continue;
            }

            $billingProvider = $user->careTeamMembers
                ->where('type', 'billing_provider')
                ->first();

            if ($billingProvider) {
                $billingProvider->alert = true;
                $billingProvider->save();
            }
        }
    }
}
