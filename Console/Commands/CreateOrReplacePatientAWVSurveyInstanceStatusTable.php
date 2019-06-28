<?php

namespace CircleLinkHealth\Customer\Console\Commands;

use Illuminate\Console\Command;

class CreateOrReplacePatientAWVSurveyInstanceStatusTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:CreateOrReplacePatientAWVSurveyInstanceStatusViewTable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Or Replace Patient AWV Survey Instance Status ViewTable. This will allow us to retrieve data from AWV in CPM regarding Survey Instances';

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
        $viewName = 'patient_awv_survey_instance_status_view';
        \DB::statement("DROP VIEW IF EXISTS ${viewName}");
        \DB::statement("
        CREATE VIEW ${viewName}
        AS
        SELECT
u.id as patient_id,
u.display_name as patient_name,
us.survey_id,
us.survey_instance_id,
si.year as year,
us.status as status
from users u
RIGHT JOIN users_surveys us ON u.id=us.user_id
LEFT JOIN survey_instances si ON si.id=us.survey_instance_id
LEFT JOIN surveys s ON s.id=us.survey_id;
      ");
    }
}
