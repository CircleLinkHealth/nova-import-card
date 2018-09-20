<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateOrReplacePatientsCcmViewTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:CreateOrReplacePatientsCcmViewTable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or Replace Patients CCM View Table (patients_ccm_view)';

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
        $viewName = "patients_ccm_view";
        \DB::statement("DROP VIEW IF EXISTS $viewName");
        \DB::statement("
        CREATE VIEW $viewName
        AS
        SELECT DISTINCT u.id
        FROM users u 
        JOIN ccd_problems ccd on u.id = ccd.patient_id
        JOIN cpm_problems cpm on ccd.cpm_problem_id = cpm.id
        WHERE u.deleted_at is null and ccd.deleted_at is null and ccd.is_monitored = 1 and cpm.is_behavioral = 0 
        ORDER BY u.id;
		");
    }
}
