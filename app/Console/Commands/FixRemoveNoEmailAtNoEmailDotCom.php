<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixRemoveNoEmailAtNoEmailDotCom extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:remove-no-email-at-no-email-dot-com';

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
        User::withTrashed()->where('email', 'like', '%@noemail.com%')->chunkById(500, function ($users) {
            foreach ($users as $user) {
                $email = "u{$user->id}@careplanmanager.com";

                if (Str::contains($user->username, ['@']) && ! Str::contains($user->username, '@noemail')) {
                    $email = $user->username;
                }

                $this->warn("Saving user[$user->id] $email");
                $user->email = $email;
                $user->save();
            }
        });

        Enrollee::where('email', 'like', '%noemail%')->chunkById(500, function ($enrollees) {
            foreach ($enrollees as $enrollee) {
                $email = "e{$enrollee->id}@careplanmanager.com";

                $this->warn("Saving enrollee[$enrollee->id] $email");
                $enrollee->email = $email;
                $enrollee->save();
            }
        });
    }
}
