<?php

use App\User;
use Carbon\Carbon;
use Tests\HandlesUsersAndCarePlans;

class NurseWorkScheduleTest extends TestCase
{
    use HandlesUsersAndCarePlans;

    public function test_main()
    {
        $nurse = $this->createUser(9, 'care-center');
        $this->userLogin($nurse);

        $this->nurse_sees_account_button_and_schedule($nurse);
        $this->provider_does_not_see_work_schedule();

        $this->nurse_stores_windows($nurse);


    }

    public function nurse_sees_account_button_and_schedule(User $nurse)
    {
        $this->actingAs($nurse)
            ->visit(route('patients.dashboard'))
            ->see($nurse->full_name)
            ->see('work-schedule-link');

        //By default PHPUnit fails the test if the output buffer wasn't closed.
        //So we're adding this to make the test work.
        ob_end_clean();
    }

    public function provider_does_not_see_work_schedule()
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

    public function nurse_stores_windows(User $nurse)
    {
        $date = Carbon::now()->addWeek(1)->format('m-d-Y');
        $timeStart = '09:00:00';
        $timeEnd = '19:00:00';

        $this->actingAs($nurse)
            ->visit(route('care.center.work.schedule.index'))
            ->type($date, 'date')
            ->type($timeStart, 'window_time_start')
            ->type($timeEnd, 'window_time_end')
            ->press('store-window')
            ->seeInDatabase('nurse_contact_window', [
                'nurse_info_id' => $nurse->nurseInfo->id,
            ]);
    }
}
