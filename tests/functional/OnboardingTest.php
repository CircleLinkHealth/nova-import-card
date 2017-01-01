<?php

use App\Entities\Invite;
use App\User;
use Faker\Factory;
use Tests\Helpers\FormRequests\Onboarding\LocationFormRequest;

class OnboardingTest extends TestCase
{
    /**
     * @var Faker\Factory $faker
     */
    protected $faker;

    /**
     * @var User $provider
     */
    protected $provider;

//    public function test_onboarding_process()
//    {
//        $this->it_stores_practice_lead();
//        $this->it_stores_a_practice(3);
//        $this->it_stores_locations();
//    }
//
//    public function it_stores_practice_lead()
//    {
//        $firstName = $this->faker->firstName;
//        $lastName = $this->faker->lastName;
//        $email = $this->faker->email;
//        $password = $this->faker->password;
//
//
//        $this->visit(route('get.onboarding.create.program.lead.user', [
//            'code' => $this->invite->code,
//        ]))
//            ->type($firstName, 'firstName')
//            ->type($lastName, 'lastName')
//            ->type($email, 'email')
//            ->type($password, 'password')
//            ->press('Next')
//            ->seeInDatabase('users', [
//                'first_name' => $firstName,
//                'last_name'  => $lastName,
//                'email'      => $email,
//            ])
//            ->seePageIs(route('get.onboarding.create.practice'));
//
//        $this->provider = User::whereEmail($email)->first();
//
//        $this->assertTrue(Hash::check($password, $this->provider->password));
//
//        $this->assertTrue($this->provider->hasRole('practice-lead'));
//    }
//
//    public function it_stores_a_practice($numberOfLocations)
//    {
//        $name = $this->faker->company;
//
//        $this->actingAs($this->provider)
//            ->visit(route('get.onboarding.create.practice'))
//            ->type($name, 'name')
//            ->press('Next');
//
//        $this->practice = Practice::whereUserId($this->provider->id)->first();
//
//        $this->seeInDatabase('practices', [
//            'name'         => str_slug($name),
//            'display_name' => $name,
//            'user_id'      => $this->provider->id,
//        ]);
//    }
//
//    public function it_stores_locations()
//    {
//        //May make this dynamic later, but for now just create one location
//        $numberOfLocations = 1;
//
//        $this->actingAs($this->provider)
//            ->visit(route('get.onboarding.create.locations', [
//                'practiceId' => $this->practice->id,
//            ]));
//
//        for ($i = 0; $i <= $numberOfLocations; $i++) {
//            $name = $this->faker->streetAddress;
//            $addrLine2 = 'PO BOX: 500';
//            $city = $this->faker->city;
//            $state = 'NJ';
//            $postalCode = $this->faker->postcode;
//            $phone = $this->faker->phoneNumber;
//
//            $this->type($name, "locations[$i][name]")
//                ->type($name, "locations[$i][address_line_1]")
//                ->type($addrLine2, "locations[$i][address_line_2]")
//                ->type($city, "locations[$i][city]")
//                ->type($state, "locations[$i][state]")
//                ->type($postalCode, "locations[$i][postal_code]")
//                ->type($phone, "locations[$i][phone]")
//                ->press('submit');
//        }
//    }

    /**
     * Check that the form to create a User is there.
     */
    public function test_it_shows_create_lead_user_form()
    {
        $this->visit(route('get.onboarding.create.program.lead.user', [
            'code' => $this->invite->code,
        ]))
            ->see('firstName')
            ->see('lastName')
            ->see('email')
            ->see('password');
    }


    /**
     * Check that the form to create a User is there.
     */
    public function test_it_shows_403_unauthorized_if_no_code_present()
    {
        $this->expectException(\Illuminate\Foundation\Testing\HttpException::class);

        $this->visit(route('get.onboarding.create.program.lead.user', [
            'code' => 'q',
        ]))->seeStatusCode(403);
    }

    public function test_post_locations()
    {
        (new LocationFormRequest)->post();
        $this->call('POST', route('post.onboarding.store.locations'), []);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->invite = factory(Invite::class)->create();
    }

}
