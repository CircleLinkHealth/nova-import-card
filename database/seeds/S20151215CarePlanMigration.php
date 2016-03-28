<?php

use App\CarePlan;
use App\CareSection;
use App\CareItem;
use App\CarePlanCareSection;
use App\CarePlanItem;
use App\CPRulesUCP;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\User;
use App\WpUser;
use App\WpUserMeta;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20151215CarePlanMigration extends Seeder {


    public function run()
    {

        $this->command->info('truncate tables.....');
        DB::connection('mysql_no_prefix')->statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::connection('mysql_no_prefix')->table('care_item_user_values')->truncate();
        DB::connection('mysql_no_prefix')->table('care_item_care_plan')->truncate();
        DB::connection('mysql_no_prefix')->table('care_plan_care_section')->truncate();
        DB::connection('mysql_no_prefix')->table('care_items')->truncate();
        DB::connection('mysql_no_prefix')->table('care_plans')->truncate();
        DB::connection('mysql_no_prefix')->table('care_sections')->truncate();
        DB::connection('mysql_no_prefix')->statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info('done truncating');

        // hack fix for bad data, missing qid for 5 programs on prod
        $rows = DB::update("UPDATE rules_items SET qid = 25 WHERE items_text = 'Smoking'");
        $this->command->info('UPDATE rules_items..... rows affected =' . $rows);
        // fix for time tracking rules
        $rows = DB::update("UPDATE lv_rules_intr_conditions SET value = 'care-center' WHERE value LIKE '%care_center%'");
        $this->command->info('UPDATE lv_rules_intr_conditions..... rows affected =' . $rows);

        $this->call('database\seeds\S20151215CarePlanMigration1');
        $this->command->info('Part 1 Completed Successfully');

        $this->call('database\seeds\S20151215CarePlanMigration2');
        $this->command->info('Part 2 Completed Successfully');

        $this->call('database\seeds\S20151215CarePlanMigration3');
        $this->command->info('Part 3 Completed Successfully');

        $this->call('database\seeds\S20151215CarePlanMigration4');
        $this->command->info('Part 4 Completed Successfully');

        $this->call('database\seeds\S20151215CarePlanMigration5');
        $this->command->info('Part 5 Completed Successfully');
        die('done!');

    }
}