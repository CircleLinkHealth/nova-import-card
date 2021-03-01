<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\DTO;

use Illuminate\Contracts\Support\Arrayable;

class PostmarkCallbackInboundData implements Arrayable
{
    const CANCELLATION_FORMATTED_KEY = 'cancelReason';
    const CANCELLATION_REASON_KEY    = 'Cancel/Withdraw Reason';

    private string $callerId;
    private string $from;
    private string $isRecId;
    private string $message;
    private string $messageId;
    private string $phone;
    private string $primary;
    private string $ptn;
    private array $rawInboundDataArray;
    private string $taken;

    /**
     * PostmarkCallbackInboundData constructor.
     */
    public function __construct(array $rawInboundDataArray)
    {
        $this->rawInboundDataArray = $rawInboundDataArray;
        $this->from                = $rawInboundDataArray['From'];
        $this->phone               = $rawInboundDataArray['Phone'];
        $this->ptn                 = $rawInboundDataArray['Ptn'];
        $this->message             = $rawInboundDataArray['Msg'];
        $this->primary             = $rawInboundDataArray['Primary'];
        $this->messageId           = $rawInboundDataArray['Msg ID'];
        $this->isRecId             = $rawInboundDataArray['IS Rec #'];
        $this->callerId            = $rawInboundDataArray['Clr ID'];
        $this->taken               = $rawInboundDataArray['Taken'];
    }

    /**
     * @return mixed|null
     */
    public function callbackCancellationMessage()
    {
        return $this->rawInboundDataArray[self::CANCELLATION_REASON_KEY] ?? null;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->$key ?? null;
    }

    public function rawInboundCallbackData(): array
    {
        return $this->rawInboundDataArray;
    }

    public function toArray()
    {
        return [
            'from'                           => $this->from,
            'phone'                          => $this->phone,
            'ptn'                            => $this->ptn,
            'message'                        => $this->message,
            'primary'                        => $this->primary,
            'messageId'                      => $this->messageId,
            'isRecId'                        => $this->isRecId,
            'callerId'                       => $this->callerId,
            'taken'                          => $this->taken,
            self::CANCELLATION_FORMATTED_KEY => $this->callbackCancellationMessage(),
        ];
    }
}
