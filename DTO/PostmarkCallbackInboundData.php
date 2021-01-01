<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\DTO;

use Illuminate\Contracts\Support\Arrayable;

class PostmarkCallbackInboundData implements Arrayable
{
    const CANCELLATION_FORMATTED_KEY = 'cancelReason';
    const CANCELLATION_REASON_KEY    = 'Cancel/Withdraw Reason';
    /**
     * @var mixed
     */
    private $callerId;
    /**
     * @var mixed
     */
    private $from;
    /**
     * @var mixed
     */
    private $isRecId;
    /**
     * @var mixed
     */
    private $message;
    /**
     * @var mixed
     */
    private $messageId;
    /**
     * @var mixed
     */
    private $phone;
    /**
     * @var mixed
     */
    private $primary;
    /**
     * @var mixed
     */
    private $ptn;
    private array $rawInboundDataArray;
    /**
     * @var mixed
     */
    private $taken;

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
        if ( ! isset($this->rawInboundDataArray[self::CANCELLATION_REASON_KEY])) {
            return null;
        }

        return $this->rawInboundDataArray[self::CANCELLATION_REASON_KEY];
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->$key ?? null;
    }

    /**
     * @return array
     */
    public function rawInboundCallbackData()
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
