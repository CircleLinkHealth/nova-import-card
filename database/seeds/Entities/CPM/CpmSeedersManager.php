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
        define('DEFAULT_LEGACY_CARE_PLAN_ID', 10);

        Model::unguard();

        DB::transaction(function () {
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
        });


        /********/
        DB::transaction(function () {
            $this->call(CcdImporterSeedersManager::class);
            $this->command->info(CcdImporterSeedersManager::class . ' ran.');
        });

        DB::transaction(function () {
            //Add care_item_id to problems
            foreach (\App\Models\CPM\CpmProblem::all() as $problem) {
                $careItem = \App\CareItem::whereName($problem->care_item_name)->first();

                $cpmProblem = \App\Models\CPM\CpmProblem::updateOrCreate(['care_item_name' => $problem->care_item_name], [
                    'care_item_id' => $careItem->id,
                ]);

                //get the details
                $detailsId = \App\CareItem::whereParentId($careItem->id)->whereDisplayName('Details')->first()->id;
                $instruction = \App\CarePlanItem::whereItemId($detailsId)->wherePlanId(DEFAULT_LEGACY_CARE_PLAN_ID)->whereNotNull('meta_value')->first();

                if (!empty($instruction)) {
                    $instruction = \App\Models\CPM\CpmInstruction::create([
                        'name' => $instruction->meta_value
                    ]);
                    
                    $cpmProblem->cpmInstructions()->attach($instruction);
                }

                $careItem->type = \App\Models\CPM\CpmProblem::class;
                $careItem->type_id = $cpmProblem->id;
                $careItem->save();
            }
            $this->command->info('Added care_item_id to problems');
        });


        try {
            DB::transaction(function () {
                $this->call(DefaultCarePlanTemplateSeeder::class);
                $this->command->info(DefaultCarePlanTemplateSeeder::class . ' ran.');
            });
        } catch (\Exception $e) {
            $this->command->error($e);
        }

        Log::notice('Seeder ' . self::class . ' was ran successfully.');
    }

}