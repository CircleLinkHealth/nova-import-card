<?php

use Tests\HandlesUsersAndCarePlans;

class NurseWorkScheduleTest extends TestCase
{
    use HandlesUsersAndCarePlans;

    public function test_nurse_sees_account_button_and_schedule()
    {
        $nurse = $this->createUser(9, 'care-center');

        $this->userLogin($nurse);

        $this->actingAs($nurse)
            ->visit(route('patients.dashboard'))
            ->see($nurse->full_name)
            ->see('work-schedule-link');

        //By default PHPUnit fails the test if the output buffer wasn't closed.
        //So we're adding this to make the test work.
        ob_end_clean();
    }

    public function test_provider_does_not_see_work_schedule()
    {
        $provider = $this->createUser(9, 'provider');

        $this->userLogin($provider);

        $this->actingAs($provider)
            ->visit(route('patients.dashboard'))
            ->see($provider->full_name)
            ->dontSee('work-schedule-link');

        //By default PHPUnit fails the test if the output buffer wasn't closed.
        //So we're adding this to make the test work.
        ob_end_clean();
    }
}
