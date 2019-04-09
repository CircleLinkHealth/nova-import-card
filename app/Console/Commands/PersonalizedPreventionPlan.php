<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PersonalizedPreventionPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PersonalizedPreventionPlan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create PPP View Table';

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
         u.display_name,
         pi.birth_date,
         u.address,
         u.address2,
         pctm.member_user_id AS billing_provider,
         us.survey_id,
         us.status AS survey_status,
         an.question_id,
         an.value_1
      
       FROM 
         users u 
         
       LEFT JOIN users_surveys us ON u.id = us.user_id
       LEFT JOIN patient_info pi ON u.id = pi.user_id
       LEFT JOIN answers an ON u.id = an.user_id
       LEFT JOIN patient_care_team_members pctm ON u.id = pctm.user_id
       
       WHERE  us.status = 'completed' 
       AND pctm.type = 'billing_provider'
      
       ");

    }
}
