<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Jobs;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Notifications\Channels\CustomTwilioChannel;
use App\SelfEnrollment\Helpers;
use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SendInvitation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var array|string[]
     */
    private $channels;
    /**
     * @var string
     */
    private $color;
    /**
     * @var int
     */
    private $invitationsBatchId;
    /**
     * @var bool
     */
    private $isReminder;
    /**
     * @var bool
     */
    private $isSurveyOnlyUser;

    /**
     * A signed URL for enrollables to login to take self enrollment survey.
     *
     * @var string|null
     */
    private $link;

    /**
     * @var string
     */
    private $shortUrl;

    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     */
    public function __construct(
        User $user,
        int $invitationsBatchId,
        ?string $color = SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
        bool $isReminder = false,
        array $channels = ['mail', CustomTwilioChannel::class]
    ) {
        $this->user               = $user;
        $this->invitationsBatchId = $invitationsBatchId;
        $this->isReminder         = $isReminder;
        $this->color              = $color;
        $this->channels           = $channels;
    }

    public function getLink(): ?string
    {
        return $this->link;
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
        if ( ! is_null($this->shortUrl)) {
            return $this->shortUrl;
        }

        $url = URL::temporarySignedRoute('invitation.enrollment.loginForm', now()->addHours(48), $this->getSignedRouteParams());

        if (empty($urlToken = Helpers::getTokenFromUrl($url))) {
            throw new \Exception("`urlToken` cannot be empty. User ID {$this->user->id}");
        }

        $notifiable = $this->user;

        if ($this->user->isSurveyOnly()) {
            $this->user->loadMissing('enrollee');
            $notifiable = $this->user->enrollee;
        }

        $notifiable->enrollmentInvitationLinks()->create([
            'link_token'       => $urlToken,
            'batch_id'         => $this->invitationsBatchId,
            'url'              => $url,
            'manually_expired' => false,
            'button_color'     => $this->color,
        ]);

        $this->link     = $url;
        $this->shortUrl = shortenUrl(url($url));

        return $this->shortUrl;
    }

    private function getSignedRouteParams(): array
    {
        return [
            'enrollable_id'  => $this->user->id,
            'is_survey_only' => $this->user->isSurveyOnly(),
        ];
    }

    private function sendInvite(string $link)
    {
        $this->user->notify(new SelfEnrollmentInviteNotification($link, $this->isReminder, $this->channels));
    }

    private function shouldRun(): bool
    {
        //If an invitation exists already, it means the patient has already been invided and we do not want to invite them again
        if ($this->user->enrollmentInvitationLinks()->exists()) {
            return false;
        }

        if ( ! in_array($this->color, [
            SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            SelfEnrollmentController::RED_BUTTON_COLOR,
        ])) {
            throw new \InvalidArgumentException("Invalid color `{$this->color}`. Valid values are `".SelfEnrollmentController::RED_BUTTON_COLOR.'` and `'.SelfEnrollmentController::DEFAULT_BUTTON_COLOR.'`.');
        }

        return true;
    }
}
