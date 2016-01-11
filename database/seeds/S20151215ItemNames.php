<?php

use App\CarePlan;
use App\CareSection;
use App\CareItem;
use App\CarePlanCareSection;
use App\CareItemCarePlan;
use App\CPRulesUCP;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\User;
use App\WpUser;
use App\WpUserMeta;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20151215ItemNames extends Seeder {


    public function run()
    {

        $this->call('database\seeds\S20151215ItemNames1');
        $this->command->info('Part 1 Completed Successfully');
        //die('d');

        $this->call('database\seeds\S20151215ItemNames2');
        $this->command->info('Part 2 Completed Successfully');
        //die('d');

        $this->call('database\seeds\S20151215ItemNames3');
        $this->command->info('Part 3 Completed Successfully');

        $this->call('database\seeds\S20151215ItemNames4');
        $this->command->info('Part 4 Completed Successfully');
        die('done!');

    }
}