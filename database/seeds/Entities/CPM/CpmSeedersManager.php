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

        try {
            //delete all instructions
            $instructions = \App\Models\CPM\CpmInstruction::all();

            foreach ($instructions as $i) {
                $i->delete();
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \App\Models\CPM\CpmInstruction::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            DB::transaction(function () {
                $this->call(CpmLifestyleSeeder::class);
                $this->command->info(CpmLifestyleSeeder::class . ' ran.');
            });

            DB::transaction(function () {
                $this->call(CpmMedicationGroupsSeeder::class);
                $this->command->info(CpmMedicationGroupsSeeder::class . ' ran.');
            });

            DB::transaction(function () {
                $this->call(CpmSymptomsSeeder::class);
                $this->command->info(CpmSymptomsSeeder::class . ' ran.');
            });

            DB::transaction(function () {
                $this->call(CpmBiometricsSeeder::class);
                $this->command->info(CpmBiometricsSeeder::class . ' ran.');
            });

            DB::transaction(function () {
                $this->call(CpmMiscSeeder::class);
                $this->command->info(CpmMiscSeeder::class . ' ran.');
            });

            /********/
            DB::transaction(function () {
                $this->call(CcdImporterSeedersManager::class);
                $this->command->info(CcdImporterSeedersManager::class . ' ran.');
            });

            DB::transaction(function () {
                $this->call(CpmProblemsSeeder::class);
            });


            DB::transaction(function () {
                $this->call(DefaultCarePlanTemplateSeeder::class);
                $this->command->info(DefaultCarePlanTemplateSeeder::class . ' ran.');
            });
        } catch (\Exception $e) {
            $this->command->error($e);
        }

        if ($this->command->confirm('Do you want to add Data Migration Helper fields to care_items, care_item_care_plan, and care_item_user_values? 
         Do this if you want to migrate existing patient data. [y|N]')
        ) {
            $this->call(DataMigrationHelperFieldsSeeder::class);
            $this->command->info(DataMigrationHelperFieldsSeeder::class . ' ran.');

            DB::transaction(function () {
                $this->call(UserBiometricsSeeder::class);
                $this->command->info(UserBiometricsSeeder::class . ' ran.');
            });
        }

        if ($this->command->confirm('Do you want to truncate all cpm_****_users tables?
         Do this to get a fresh user values migration. [y|N]')
        ) {
            $this->call(TruncateCpmRelationshipTables::class);
            $this->command->info(TruncateCpmRelationshipTables::class . ' ran.');
        }

        if ($this->command->confirm('Do you want to migrate your care_item_user_values table over to cpm_****_users tables?
         This may take a while depending on how many users your DB has [y|N]')
        ) {
            try {
                $this->call(MigrateCarePlanDataMay16::class);
                $this->command->info(MigrateCarePlanDataMay16::class . ' ran.');

                $this->call(MigrateUserCpmProblemsInstructions::class);
                $this->command->info(MigrateUserCpmProblemsInstructions::class . ' ran.');
            } catch (\Exception $e) {
                Log::critical($e);
                $this->command->error($e);
            }
        }

        Log::notice('Seeder ' . self::class . ' was ran successfully.');
    }

}