<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Console;

use CircleLinkHealth\SamlSp\Entities\SamlUser;
use Illuminate\Console\Command;

class RegisterSamlUserMapping extends Command
{
    /**
     * @var string
     */
    protected $description = 'Register a user map for SAML SSO.';

    /**
     * @var string
     */
    protected $signature = 'saml:register-user {cpmUserId} {idp} {idpUserId}';

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
        SamlUser::updateOrCreate(
            [
                'idp'         => $this->argument('idp'),
                'idp_user_id' => $this->argument('idpUserId'),
            ],
            [
                'cpm_user_id' => $this->argument('cpmUserId'),
            ]
        );
        $this->info('Done');

        return 0;
    }
}
