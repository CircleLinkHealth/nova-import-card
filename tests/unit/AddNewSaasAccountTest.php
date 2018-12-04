<?php

namespace Tests\Unit;

use App\Role;
use App\SaasAccount;
use App\User;
use App\Notifications\SAAS\SendInternalUserSignupInvitation;
use App\Practice;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddNewSaasAccountTest
{
    use UserHelpers, WithFaker;
    private $adminUser;
    private $adminPractice;

    /**
     * A basic test example.
     *
     * @return void
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

        $user = User::whereEmail($adminEmails[0])->first();
        $saasAccount = SaasAccount::whereName($name)->first();

        $this->assertNotNull($saasAccount);
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(Role::whereName('saas-admin')->first()->name));
        $this->assertEquals($saasAccount->id, $user->saas_account_id);

        Notification::assertSentTo(
            [$user],
            SendInternalUserSignupInvitation::class
        );

        $result = new \stdClass();
        $result->user = $user;
        $result->saasAccount = $saasAccount;

        return $result;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->adminPractice = factory(Practice::class)->create([]);
        $this->adminUser     = $this->createUser($this->adminPractice->id, 'administrator');
    }
}
