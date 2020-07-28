<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\CarePerson;
use Illuminate\Console\Command;

class FixToledoMakeSureProviderMatchesPracticePull extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make sure patients\' billing provider mathces practice pull data.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:toledo:make-sure-provider-matches-practice-pull';

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
     * @return int
     */
    public function handle()
    {
        \DB::table('users')
            ->select([
                'users.id',
                'patient_care_team_members.user_id',
                'patient_info.user_id',
                'patient_info.mrn_number',
                'practice_pull_demographics.mrn',
                'patient_care_team_members.member_user_id',
                'practice_pull_demographics.billing_provider_user_id',
            ])
            ->where('program_id', 235)
            ->join('patient_care_team_members', function ($join) {
                $join->on('users.id', '=', 'patient_care_team_members.user_id')
                    ->where('type', '=', CarePerson::BILLING_PROVIDER)
                    ->whereNotNull('member_user_id');
            })
            ->join('patient_info', 'patient_info.user_id', '=', 'users.id')
            ->join('practice_pull_demographics', function ($join) {
                $join->on('practice_pull_demographics.mrn', '=', 'patient_info.mrn_number')
                    ->where('practice_id', '=', 235);
            })->where('patient_care_team_members.member_user_id', '!=', 'practice_pull_demographics.billing_provider_user_id')
            ->chunkById(500, function ($users) {
                \DB::transaction(function () use ($users) {
                    foreach ($users as $user) {
                        if ($user->billing_provider_user_id) {
                        }
                    }
                });
            }, 'users.id');
    }
}
