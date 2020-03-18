<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Call;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Tests\Helpers\CustomerTestCaseHelper;

class PopulateNursePerformanceTable extends Seeder
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
        
        $this->command->info('sex');
    }
    
    private function createFakePatients()
    {
        $this->command->warn('Creating Fake Patients');
        
        PageTimer::select('patient_id')
                 ->doesntHave('patient')
                 ->where('patient_id', '!=', 0)
                 ->distinct()
                 ->get()
                 ->unique('patient_id')
                 ->each(
                     function ($id) {
                         $fakePatient     = $this->patient();
                         $fakePatient->id = $id->patient_id;
                         $fakePatient->save();
                     }
                 );
    }
    
    private function createFakeProviders()
    {
        $this->command->warn('Creating Fake Providers');
    
        PageTimer::select('provider_id')
                 ->doesntHave('provider')
                 ->distinct()
                 ->get()
                 ->unique('provider_id')
                 ->each(
                     function ($id) {
                         $fakeProvider     = $this->provider();
                         $fakeProvider->id = $id->provider_id;
                         $fakeProvider->save();
                     }
                 );
    }
    
    private function importDBDumps()
    {
        $this->tables()->each(
            function ($table) {
                $this->command->warn("Importing $table");
                
                $this->importFromJson($table);
//            DB::table($table)->truncate();
//            DB::unprepared(file_get_contents($this->pathToFile($table)));
            }
        );
    }
    
    private function pathToFile(string $filename)
    {
        return storage_path("testdata/$filename.json");
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
    
    private function importFromJson($table)
    {
        $this->command->warn("Inserting $table");
        foreach (json_decode(file_get_contents($this->pathToFile($table)), true) as $row) {
//            $this->command->warn("Creating $table:{$row['id']}");
            DB::table($table)->updateOrInsert(['id' => $row['id']], $row);
        }
    }
}
