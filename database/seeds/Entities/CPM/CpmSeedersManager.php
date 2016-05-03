<?php
use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/21/16
 * Time: 12:20 PM
 */
class CpmSeedersManager extends \Illuminate\Database\Seeder
{
    public function run()
    {
        //Problems seeder already exists
        
        Model::unguard();

        $this->call(CpmLifestyleSeeder::class);
        $this->command->info(CpmLifestyleSeeder::class . ' ran.');

        $this->call(CpmMedicationGroupsSeeder::class);
        $this->command->info(CpmMedicationGroupsSeeder::class . ' ran.');

        $this->call(CpmSymptomsSeeder::class);
        $this->command->info(CpmSymptomsSeeder::class . ' ran.');

        $this->call(CpmBiometricsSeeder::class);
        $this->command->info(CpmBiometricsSeeder::class . ' ran.');
        
        $this->call(CpmMiscSeeder::class);
        $this->command->info(CpmMiscSeeder::class . ' ran.');

        /********/
        
        $this->call(CcdImporterSeedersManager::class);
        $this->command->info(CcdImporterSeedersManager::class . ' ran.');

        //Add care_item_id to problems
        foreach (\App\Models\CPM\CpmProblem::all() as $problem)
        {
            $careItem = \App\CareItem::whereName($problem->care_item_name)->first();

            $cpmProblem = \App\Models\CPM\CpmProblem::updateOrCreate(['care_item_name' => $problem->care_item_name], [
                'care_item_id' => $careItem->id,
            ]);

            $careItem->type = \App\Models\CPM\CpmProblem::class;
            $careItem->type_id = $cpmProblem->id;
            $careItem->save();
        }

        $this->call(DefaultCarePlanTemplateSeeder::class);
        $this->command->info(DefaultCarePlanTemplateSeeder::class . ' ran.');
        

    }

}