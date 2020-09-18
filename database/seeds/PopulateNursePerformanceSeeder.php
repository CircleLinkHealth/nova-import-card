<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Tests\Helpers\CustomerTestCaseHelper;

class PopulateNursePerformanceSeeder extends Seeder
{
    use CustomerTestCaseHelper;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '1000M');

        Model::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->importDBDumps();
        $this->createFakePatients();
        $this->createFakeProviders();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Model::reguard();

        $this->command->info('Done');
    }

    private function createFakePatients()
    {
        $this->command->warn('Creating Fake Patients');

        PageTimer::select('patient_id')
            ->whereDoesntHave('patient', function ($q) {
                $q->withTrashed();
            })
            ->where('patient_id', '!=', 0)
            ->distinct()
            ->get()
            ->unique('patient_id')
            ->each(
                function ($id) {
                    $fakePatient = $this->patient();
                    $fakePatient->id = $id->patient_id;
                    $fakePatient->save();
                }
            );
    }

    private function createFakeProviders()
    {
        $this->command->warn('Creating Fake Nurses');

        PageTimer::select(['provider_id', 'start_time'])
            ->whereDoesntHave('logger', function ($q) {
                $q->withTrashed();
            })
            ->distinct()
            ->get()
            ->unique('provider_id')
            ->each(
                function ($pageTimer) {
                    $fakeUser = $this->careCoach();
                    $fakeUser->id = $pageTimer->provider_id;
                    $fakeUser->save();
                    $this->updateCreateWorkHours($fakeUser, $pageTimer->start_time);
                    $this->command->warn('Updating WorkHours');
                }
            );
    }

    private function importDBDumps()
    {
        $this->tables()->each(
            function ($table) {
                $this->command->warn("Importing $table");

//                $this->importFromJson($table);
                DB::table($table)->truncate();
                DB::unprepared(file_get_contents($this->pathToFile($table)));
            }
        );
    }

    private function importFromJson($table)
    {
        $this->command->warn("Inserting $table");
        foreach (json_decode(file_get_contents($this->pathToFile($table)), true) as $row) {
//            $this->command->warn("Creating $table:{$row['id']}");
            DB::table($table)->updateOrInsert(['id' => $row['id']], $row);
        }
    }

    private function pathToFile(string $filename)
    {
        return storage_path("testdata/$filename.sql");
    }

    private function tables()
    {
        return collect(
            [
                (new Call())->getTable(),
                (new PageTimer())->getTable(),
                (new NurseContactWindow())->getTable(),
                (new PatientMonthlySummary())->getTable(),
            ]
        );
    }

    /**
     * @param $pageTimerStartTime
     */
    private function updateCreateWorkHours(User $fakeUser, $pageTimerStartTime)
    {
        $faker                    = Factory::create();
        $date                     = \Carbon\Carbon::parse($pageTimerStartTime);
        $workWeekStart            = $date->copy()->startOfWeek()->toDateString();
        $dayOfWeek                = carbonToClhDayOfWeek($date->dayOfWeek);
        $randomCommittedWorkHours = $faker->randomElements([2, 4, 5, 6]);

        $fakeUser->nurseInfo->windows()->updateOrCreate(
            [
                'date' => $date->toDateString(),
            ],
            [
                'day_of_week'       => $dayOfWeek,
                'window_time_start' => '11:00',
                'window_time_end'   => '18:00',
                'repeat_frequency'  => 'does_not_repeat',
            ]
        );

        $fakeUser->nurseInfo->workhourables()->updateOrCreate(
            [
                'work_week_start' => $workWeekStart,
            ],
            [
                strtolower(clhDayOfWeekToDayName($dayOfWeek)) => $randomCommittedWorkHours[0],
            ]
        );
    }
}
