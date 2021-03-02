<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Jobs;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\Helpers;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController;
use CircleLinkHealth\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use function PHPUnit\Framework\isEmpty;

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
        array $channels = ['mail', 'twilio']
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

        $url = URL::temporarySignedRoute('invitation.enrollment.loginForm', now()->addWeeks(2), $this->getSignedRouteParams());

        if (empty($urlToken = Helpers::getTokenFromUrl($url))) {
            throw new \Exception("`urlToken` cannot be empty. User ID {$this->user->id}");
        }

        $notifiable = $this->getEnrolleeToNotify($this->user, $this->isReminder);

        if (! $notifiable){
            return '';
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
        if (empty($link)){
            return;
        }
        $this->user->notify(new SelfEnrollmentInviteNotification($link, $this->isReminder, $this->channels));
    }

    private function shouldRun(): bool
    {
        if (Patient::UNREACHABLE !== $this->user->getCcmStatus()) {
            return false;
        }

        if (empty($this->user->billingProviderUser())) {
            return false;
        }

        return true;
    }

    private function getEnrolleeToNotify(User $user, bool $isReminder)
    {
        if ($user->enrollee && Helpers::canSendSelfEnrollmentInvitation($user->enrollee, $isReminder)){
            return $user->enrollee;
        }

        $user->load([
            'enrollee' => function($enrollee) use ($isReminder){
                $enrollee->canSendSelfEnrollmentInvitation(!$isReminder);
            },
        ]);

        return $user->enrollee ?? null;
    }
}
