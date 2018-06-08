<?php

use App\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class UserScrambler extends Seeder
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $this->throwExceptionIfProduction();

        $limit = ini_get('memory_limit'); // retrieve the set limit
        ini_set('memory_limit', -1); // remove memory limit

        config(['mail.driver' => 'log']);
        $this->scrambleDB();

        ini_set('memory_limit', $limit);
    }

    public function scrambleDB()
    {
        //scramble users
        User::orderBy('id')
            ->withTrashed()
            ->with(['practices'])
            ->whereDoesntHave('practices', function ($q) {
                $q->where('id', 8);
            })
            ->chunk(500, function ($users) {
                foreach ($users as $user) {
                    $user->scramble();
                }
            });
    }

    /**
     * @throws Exception
     */
    private function throwExceptionIfProduction()
    {
        if (in_array(app()->environment(), [
            'production',
            'worker',
        ])) {
            $env = app()->environment();

            throw new \Exception("Not a good idea to run this on environment $env");
        }

        if (in_array(env('DB_DATABASE'), [
            'cpm_production',
        ])) {
            $db = env('DB_DATABASE');

            throw new \Exception("Not a good idea to run this on DB $db");
        }
    }
}
