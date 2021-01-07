<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Jobs;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\SharedModels\Entities\EmailAddressParts;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMail;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMailRequest;
use CircleLinkHealth\SharedModels\Services\Postmark\AutoResolveCallbackRequestService;
use CircleLinkHealth\SharedModels\Services\Postmark\ScheduleCallbackAndNotifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPostmarkInboundMailJob implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    /**
     * @var bool|null
     */
    private $dbRecordId;

    private PostmarkInboundMailRequest $input;

    /**
     * Create a new job instance.
     */
    public function __construct(PostmarkInboundMailRequest $input, int $dbRecordId = null)
    {
        $this->input      = $input;
        $this->dbRecordId = $dbRecordId;
    }

    /**
     * @return string|void
     */
    public function handle()
    {
        $recordId = $this->dbRecordId ?: $this->storeRawLogs();
        $email    = $this->removeAliasFromEmail($this->input->From);

        if ( ! $email) {
            Log::error("Empty Postmark notification field:'From'. Record id $recordId");

            return;
        }

        $emailParts = $this->splitEmail($email);

        if ( ! $emailParts) {
            Log::error("Email Splitting Failed for inbound_postmark_mail [$recordId]");
            sendSlackMessage('#carecoach_ops_alerts', "Email Splitting Failed for inbound_postmark_mail [$recordId]");

            return;
        }

        if (CpmConstants::FROM_ETHAN_MAIL === $email || CpmConstants::FROM_CALLBACK_EMAIL_DOMAIN === $emailParts->domain) {
            $autoScheduleCallbackService = app(AutoResolveCallbackRequestService::class);
            $autoScheduleCallbackService->processCreateCallback($recordId);

            return;
        }

        $callbackWithNotificationSent = app(ScheduleCallbackAndNotifyService::class);
        $callbackWithNotificationSent->processCallbackAndNotify($emailParts, $recordId, $this->dbRecordId, $this->input);
    }

    private function removeAliasFromEmail(string $email)
    {
        return preg_replace('/(\+[^\@]+)/', '', $email);
    }

    private function splitEmail(string $email)
    {
        $parts = explode('@', $email);

        return new EmailAddressParts($parts[0] ?? '', $parts[1] ?? '');
    }

    private function storeRawLogs(): ?int
    {
        try {
            $result = PostmarkInboundMail::create([
                'data' => json_encode($this->input),
            ]);

            return $result->id;
        } catch (\Throwable $e) {
            Log::warning($e->getMessage());

            return null;
        }
    }
}
