<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Notifications\SAAS\SendInternalUserSignupInvitation;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\UserHelpers;

class AddNewSaasAccountTest
{
    use UserHelpers;
    use WithFaker;
    private $adminPractice;
    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminPractice = factory(Practice::class)->create([]);
        $this->adminUser     = $this->createUser($this->adminPractice->id, 'administrator');
    }

    /**
     * A basic test example.
     */
    //  commenting out because it doesn't run with sqlite. Michalis says no point fixing as we don't use saas.
//    public function test_flow()
//    {
//        $result = $this->createSaasAccountAndAdmin();
//
//
//    }

    private function createSaasAccountAndAdmin()
    {
        Notification::fake();

        $name        = $this->faker->company;
        $adminEmails = [
            $this->faker->email,
        ];

        $response = $this
            ->actingAs($this->adminUser)
            ->post(route('saas-accounts.store'), [
                'name'         => $name,
                'admin_emails' => implode(',', $adminEmails),
            ]);

        $response->assertStatus(200);

        $user        = User::whereEmail($adminEmails[0])->first();
        $saasAccount = SaasAccount::whereName($name)->first();

        $this->assertNotNull($saasAccount);
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(Role::whereName('saas-admin')->first()->name));
        $this->assertEquals($saasAccount->id, $user->saas_account_id);

        Notification::assertSentTo(
            [$user],
            SendInternalUserSignupInvitation::class
        );

        $result              = new \stdClass();
        $result->user        = $user;
        $result->saasAccount = $saasAccount;

        return $result;
    }
}
