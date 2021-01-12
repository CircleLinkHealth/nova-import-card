<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreatePersonalizedPreventionPlanViewTable extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create PPP View Table';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PersonalizedPreventionPlanController';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $viewName = 'personalized_prevention_plan';
        DB::statement("DROP VIEW IF EXISTS ${viewName}");
        DB::statement("
       CREATE VIEW ${viewName} 
       AS
       SELECT
         u.id AS user_id,
         max(u.display_name) AS display_name,
         max(pi.birth_date) AS birth_date,
         max(u.address) AS address,
         max(u.address2) AS address2,
         max(pctm.user_id) AS billing_provider,
         us.survey_id AS survey_id,
         max(us.status) AS survey_status,
         max(an.value_1->>'$.weight') weight,
         max(an.value_1->>'$.height') height,
         max(an.value_1->>'$.BMI') BMI,
         max(an.value_1->>'$.blood_pressure') blood_pressure
      
       FROM 
         users u 
         
         
       LEFT JOIN users_surveys us ON u.id = us.user_id
       LEFT JOIN patient_info pi ON u.id = pi.user_id
       LEFT JOIN answers an ON u.id = an.user_id
       LEFT JOIN patient_care_team_members pctm ON u.id = pctm.user_id
       
       WHERE  us.status = 'completed' 
       
       GROUP BY 
       u.id,
       us.survey_id
       ");
    }
}
