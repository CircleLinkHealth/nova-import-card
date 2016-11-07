<?php

use App\NurseContactWindow;
use App\User;
use Carbon\Carbon;
use Tests\Helpers\HandlesUsersAndCarePlans;

class NurseWorkScheduleTest extends TestCase
{
    use Tests\Helpers\HandlesUsersAndCarePlans;

    public function test_main()
    {
        $nurse = $this->createUser(9, 'care-center');

        $this->nurse_sees_account_button_and_schedule($nurse);
        $this->provider_does_not_see_work_schedule();

        $this->nurse_stores_and_deletes_window($nurse);

        $this->nurse_fails_to_store_and_delete_invalid_windows($nurse);

        $this->store_window($nurse, Carbon::now()->addWeek(2));
        $this->store_window($nurse, Carbon::now()->addWeek(3));

        $this->report($nurse);
    }

    protected function nurse_sees_account_button_and_schedule(User $nurse)
    {
        $this->actingAs($nurse)
            ->visit(route('patients.dashboard'))
            ->see($nurse->full_name)
            ->see('work-schedule-link');

        //By default PHPUnit fails the test if the output buffer wasn't closed.
        //So we're adding this to make the test work.
        ob_end_clean();
    }

    protected function provider_does_not_see_work_schedule()
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

    protected function nurse_stores_and_deletes_window(User $nurse)
    {
        $window = $this->store_window($nurse, Carbon::now()->addWeek(2));

        $this->delete_window($nurse, $window);

    }

    protected function store_window(
        User $nurse,
        Carbon $date,
        $valid = true,
        $timeStart = '09:00',
        $timeEnd = '19:00'
    ) {
        $this->actingAs($nurse)
            ->visit(route('care.center.work.schedule.index'))
            ->type($date->format('m-d-Y'), 'date')
            ->type($timeStart, 'window_time_start')
            ->type($timeEnd, 'window_time_end')
            ->press('store-window');

        if ($valid) {
            $this->seeInDatabase('nurse_contact_window', [
                'nurse_info_id'     => $nurse->nurseInfo->id,
                'date'              => $date->format('Y-m-d'),
                'day_of_week'       => carbonToClhDayOfWeek($date->dayOfWeek),
                'window_time_start' => $timeStart,
                'window_time_end'   => $timeEnd,
            ]);

            return $nurse->nurseInfo->windows()->first();
        }

        $this->dontSeeInDatabase('nurse_contact_window', [
            'nurse_info_id'     => $nurse->nurseInfo->id,
            'date'              => $date->format('Y-m-d'),
            'window_time_start' => $timeStart,
            'window_time_end'   => $timeEnd,
        ]);
    }

    protected function delete_window(
        User $nurse,
        NurseContactWindow $window,
        $valid = true
    ) {
        if ($valid) {
            $this->actingAs($nurse)
                ->visit(route('care.center.work.schedule.index'))
                ->click("delete-window-{$window->id}");

            $this->dontSeeInDatabase('nurse_contact_window', [
                'nurse_info_id' => $nurse->nurseInfo->id,
                'id'            => $window->id,
            ]);
        }

        if (!$valid) {
            $response = $this->call('GET', '/care-center/work-schedule/destroy/71');

            $this->actingAs($nurse)
                ->visit(route('care.center.work.schedule.index'))
                ->dontSee("delete-window-{$window->id}");

            $this->seeInDatabase('nurse_contact_window', [
                'nurse_info_id' => $nurse->nurseInfo->id,
                'id'            => $window->id,
            ]);
        }
    }

    protected function nurse_fails_to_store_and_delete_invalid_windows(User $nurse)
    {
        $this->store_window($nurse, Carbon::now(), false);
        $this->store_window($nurse, Carbon::parse('this sunday'), false);

        $date = Carbon::now();
        $timeStart = '10:00';
        $timeEnd = '13:00';

        $window = NurseContactWindow::create([
            'nurse_info_id'     => $nurse->nurseInfo->id,
            'date'              => $date->format('Y-m-d'),
            'window_time_start' => $timeStart,
            'window_time_end'   => $timeEnd,
        ]);

        $this->delete_window($nurse, $window, false);
    }

    public function report($user)
    {
        /**
         * Report stuff
         */

        //This is kinda hacky.
        //We are checking which database is being used to figure out which environment we are on.
        //This is because when testing, the APP_ENV is set to 'testing'
        $db = env('DB_DATABASE');

        $text = "
            A Nurse was created:
            login: {$user->email}
            password: password
            ";

        if (in_array($db, [
            'cpm_staging',
            'cpm_testing',
            'cpm_hotfix',
        ])) {
            Slack::to('#qualityassurance')
                ->send($text);
        }

        echo $text;
    }
}
