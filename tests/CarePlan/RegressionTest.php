<?php
use App\CLH\Repositories\UserRepository;
use App\User;
use Symfony\Component\HttpFoundation\ParameterBag;


class RegressionTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected $provider;

    public function testClhRegressionTesting()
    {
        /*
         * Since we're using DatabaseTransactions, it seems like Laravel rolls back after executing each method.
         * @todo: research what's going on and figure out if DatabaseTransactions can be rolled back after all tests run
         *
         * For let's just call it again.
         */
        $this->createProvider();

        $this->providerLogin();

        $this->createNewPatient();

    }

    public function createProvider()
    {
        $faker = Faker\Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $email = $faker->email;

        $roles = [
            \App\Role::whereName('provider')->first()->id,
        ];

        $bag = new ParameterBag([
            'user_email' => $email,
            'user_pass' => 'password',
            'display_name' => "$firstName $lastName",
            'first_name' => $firstName,
            'last_name' => $lastName,
            'user_login' => $faker->userName,
            'program_id' => 9, //testdrive
            'address' => $faker->streetAddress,
            'address2' => '',
            'city' => $faker->city,
            'state' => 'AL',
            'zip' => '12345',
            'is_auto_generated' => true,
            'roles' => $roles,
        ]);

        //create a user
        $user = (new UserRepository())->createNewUser(new User(), $bag);

        //check that it was created
        $this->seeInDatabase('users', ['user_email' => $email]);

        //check that the roles were created
        foreach ($roles as $role) {
            $this->seeInDatabase('lv_role_user', [
                'user_id' => $user->ID,
                'role_id' => $role,
            ]);
        }

        $this->provider = $user;
    }

    public function providerLogin()
    {
        $this->visit('/auth/login')
            ->see('CarePlanManager')
            ->type($this->provider->user_email, 'email')
            ->type('password', 'password')
            ->press('Log In')
            ->seePageIs('/manage-patients/dashboard');

        //By default PHPUnit fails the test if the output buffer wasn't closed.
        //So we're adding this to make the test work.
        ob_end_clean();
    }

    public function createNewPatient()
    {
        
    }
}