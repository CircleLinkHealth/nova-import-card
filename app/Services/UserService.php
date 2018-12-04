<?php namespace App\Services;

use App\CarePlanTemplate;
use App\User;
use App\Repositories\UserRepositoryEloquent;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 5:43 PM
 */
class UserService
{
    private $userRepo;

    public function __construct(UserRepositoryEloquent $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function repo()
    {
        return $this->userRepo;
    }

    /**
     * Get the User's first CarePlan, or relate the User to CLH's default CarePlan.
     *
     * @param User|null $user
     *
     * @return \App\CarePlan
     */
    public function firstOrDefaultCarePlan(User $user)
    {
        return $user->carePlan()->firstOrCreate([
            'user_id'               => $user->id,
            'care_plan_template_id' => getDefaultCarePlanTemplate()->id,
        ]);
    }
}
