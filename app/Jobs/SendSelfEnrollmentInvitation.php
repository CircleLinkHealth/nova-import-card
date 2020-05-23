<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Helpers\SelfEnrollmentHelpers;
use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use App\Notifications\SendEnrollementSms as Sms;
use App\Notifications\SendEnrollmentEmail as Email;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class SendSelfEnrollmentInvitation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var string
     */
    private $color;
    /**
     * @var bool
     */
    private $isReminder;
    /**
     * @var bool
     */
    private $isSurveyOnlyUser;

    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, ?string $color = AutoEnrollmentCenterController::DEFAULT_BUTTON_COLOR, bool $isReminder = false)
    {
        $this->user       = $user;
        $this->isReminder = $isReminder;
        $this->color      = $color;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! $this->shouldRun()) {
            return;
        }

        $this->sendInvite($this->createLink());
    }

    private function createLink(): string
    {
        $url = URL::temporarySignedRoute('invitation.enrollment.loginForm', now()->addHours(48), $this->getSignedRouteParams());

        $urlToken = SelfEnrollmentHelpers::getTokenFromUrl($url);

        if (empty($urlToken)) {
            throw new \Exception("`urlToken` cannot be empty. User ID {$this->user->id}");
        }

        $notifiable = $this->user;

        if ($this->isSurveyOnlyUser()) {
            $notifiable = Enrollee::whereUserId($this->user->id)->firstOrFail();
        }

        $notifiable->enrollmentInvitationLinks()->create([
            'link_token'       => $urlToken,
            'url'              => $url,
            'manually_expired' => false,
            'button_color'     => $this->color,
        ]);

        return shortenUrl(url($url));
    }

    private function getSignedRouteParams(): array
    {
        return [
            'enrollable_id'  => $this->user->id,
            'is_survey_only' => $this->isSurveyOnlyUser(),
        ];
    }

    private function isSurveyOnlyUser()
    {
        if ( ! $this->isSurveyOnlyUser) {
            $this->isSurveyOnlyUser = $this->user->isSurveyOnly();
        }

        return $this->isSurveyOnlyUser;
    }

    private function sendInvite(string $link)
    {
        $this->user->notify(new Email($link, $this->isReminder));
        $this->user->notify(new Sms($link, $this->isReminder));
    }

    private function shouldRun(): bool
    {
        if (App::environment(['testing'])) {
            return false;
        }

        //If an invitation exists already, it means the patient has already been invided and we do not want to invite them again
        if ($this->user->enrollmentInvitationLinks()->exists()) {
            return false;
        }

        if ( ! in_array($this->color, [
            AutoEnrollmentCenterController::DEFAULT_BUTTON_COLOR,
            AutoEnrollmentCenterController::RED_BUTTON_COLOR,
        ])) {
            throw new \InvalidArgumentException("Invalid color `{$this->color}`. Valid values are `".AutoEnrollmentCenterController::RED_BUTTON_COLOR.'` and `'.AutoEnrollmentCenterController::DEFAULT_BUTTON_COLOR.'`.');
        }

        return true;
    }
}
