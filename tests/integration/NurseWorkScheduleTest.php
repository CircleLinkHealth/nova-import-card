<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\integration;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Tests\DuskTestCase;
use Tests\Helpers\CarePlanHelpers;

class NurseWorkScheduleTest extends DuskTestCase
{
    use \CircleLinkHealth\Customer\Traits\UserHelpers;
    use CarePlanHelpers;

    public function report($user)
    {
        /**
         * Report stuff.
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
        ])) {
            sendSlackMessage('#qualityassurance', $text);
        }

        echo $text;
    }

    public function test_main()
    {
        $practice = factory(Practice::class)->create();

        $nurse = $this->createUser($practice->id, 'care-center');

        $this->nurse_sees_account_button_and_schedule($nurse);
        $this->provider_does_not_see_work_schedule();

        $this->nurse_stores_and_deletes_window($nurse);

        $this->nurse_fails_to_store_and_delete_invalid_windows($nurse);

        $this->store_window($nurse, Carbon::now()->addWeek(2));
        $this->store_window($nurse, Carbon::now()->addWeek(3));

        $this->report($nurse);
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

        if ( ! $valid) {
            $response = $this->call('GET', '/care-center/work-schedule/destroy/71');

            $this->actingAs($nurse)
                ->visit(route('care.center.work.schedule.index'))
                ->assertDontSee("delete-window-{$window->id}");

            $this->assertDatabaseHas('nurse_contact_window', [
                'nurse_info_id' => $nurse->nurseInfo->id,
                'id'            => $window->id,
            ]);
        }
    }

    protected function nurse_fails_to_store_and_delete_invalid_windows(User $nurse)
    {
        $this->store_window($nurse, Carbon::now(), false);
        $this->store_window($nurse, Carbon::parse('this sunday'), false);

        $date      = Carbon::now();
        $timeStart = '10:00';
        $timeEnd   = '13:00';

        $window = NurseContactWindow::create([
            'nurse_info_id'     => $nurse->nurseInfo->id,
            'date'              => $date->format('Y-m-d'),
            'window_time_start' => $timeStart,
            'window_time_end'   => $timeEnd,
        ]);

        $this->delete_window($nurse, $window, false);
    }

    protected function nurse_sees_account_button_and_schedule(User $nurse)
    {
        $this->browse(function ($browser) use ($nurse) {
            $browser->loginAs($nurse)
                ->visit(route('patients.dashboard'))
                ->assertPathIs('/manage-patients/dashboard')
                ->assertSee($nurse->getFullName())
                ->assertSee('Create/Edit Schedule');
        });
    }

    protected function nurse_stores_and_deletes_window(User $nurse)
    {
        $window = $this->store_window($nurse, Carbon::now()->addWeek(2));

        $this->delete_window($nurse, $window);
    }

    protected function provider_does_not_see_work_schedule()
    {
        $provider = $this->createUser(9, 'provider');

        $this->browse(function ($browser) use ($provider) {
            $browser->loginAs($provider)
                ->visit(route('patients.dashboard'))
                ->assertPathIs('/manage-patients/dashboard')
                ->assertSee($provider->getFullName())
                ->assertDontSee('Create/Edit Schedule');
        });
    }

    protected function store_window(
        User $nurse,
        Carbon $date,
        $valid = true,
        $timeStart = '09:00:00',
        $timeEnd = '19:00:00'
    ) {
        $this->browse(function ($browser) use ($nurse, $date, $valid, $timeStart, $timeEnd) {
            $browser->loginAs($nurse)
                ->visit(route('care.center.work.schedule.index'))
                ->select('day_of_week', carbonToClhDayOfWeek($date->dayOfWeek))
                ->type('window_time_start', $timeStart)
                ->type('window_time_end', $timeEnd)
                ->press('store-window');

            if ($valid) {
                $this->assertDatabaseHas('nurse_contact_window', [
                    'nurse_info_id'     => $nurse->nurseInfo->id,
                    'day_of_week'       => carbonToClhDayOfWeek($date->dayOfWeek),
                    'window_time_start' => $timeStart,
                    'window_time_end'   => $timeEnd,
                ]);

                return $nurse->nurseInfo->windows()->first();
            }

            $this->dontSeeInDatabase('nurse_contact_window', [
                'nurse_info_id'     => $nurse->nurseInfo->id,
                'window_time_start' => $timeStart,
                'window_time_end'   => $timeEnd,
            ]);
        });
    }
}
