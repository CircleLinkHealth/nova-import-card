<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SlackSlashCommands;

use App\Jobs\ExecuteArtisanCommand;
use Spatie\SlashCommand\Handlers\BaseHandler;
use Spatie\SlashCommand\Request;
use Spatie\SlashCommand\Response;

class RunCommand extends BaseHandler
{
    /**
     * If this function returns true, the handle method will get called.
     */
    public function canHandle(Request $request): bool
    {
        return in_array($request->userId, config('laravel-slack-slash-command.allowed_user_ids', []));
    }

    /**
     * Handle the given request. Remember that Slack expects a response
     * within three seconds after the slash command was issued. If
     * there is more time needed, dispatch a job.
     *
     * Example usage:
     * - command signature: nurseinvoices:create {month} {userIds} {--an-option}
     * - command line: php artisan nurseinvoices:create 2020-07-01 333,334 true
     * - slack: /run-command-staging nurseinvoices:create month=2020-07-1 userIds=333,334 --an-option=true
     */
    public function handle(Request $request): Response
    {
        $commandArgs = explode(' ', $this->request->text);
        $command     = $commandArgs[0];
        unset($commandArgs[0]);

        $args = [];
        if ( ! empty($commandArgs)) {
            foreach ($commandArgs as $arg) {
                $parts = explode('=', $arg);
                if (1 === sizeof($parts)) {
                    $args[] = $args;
                } else {
                    $args[$parts[0]] = $parts[1];
                }
            }
        }

        ExecuteArtisanCommand::dispatch($command, $args);

        $argsText = implode(' ', $commandArgs);

        return $this->respondToSlack("Command `$command $argsText` queued to be executed!");
    }
}
