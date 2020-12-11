<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;

use Illuminate\Support\Arr;

class PostmarkCallbackInboundData
{
    private array $inboundDataArray;

    /**
     * PostmarkCallbackInboundData constructor.
     */
    public function __construct(array $inboundDataArray)
    {
        $this->inboundDataArray = $inboundDataArray;
    }

    public function getInboundDataArray()
    {
        $inboundDataFormatted = [
            'from'      => $this->inboundDataArray['From'],
            'phone'     => $this->inboundDataArray['Phone'],
            'ptn'       => $this->inboundDataArray['Ptn'],
            'message'   => $this->inboundDataArray['Msg'],
            'primary'   => $this->inboundDataArray['Primary'],
            'messageId' => $this->inboundDataArray['Msg ID'],
            'isRecId'   => $this->inboundDataArray['IS Rec #'],
            'callerId'  => $this->inboundDataArray['Clr ID'],
            'taken'     => $this->inboundDataArray['Taken'],
        ];

        if (isset($this->inboundDataArray['Cancel/Withdraw Reason'])) {
            $inboundDataFormatted = Arr::add($inboundDataFormatted, 'cancelReason', $this->inboundDataArray['Cancel/Withdraw Reason']);
        }

        return $inboundDataFormatted;
    }
    
    /**
     * @param $key
     * @return mixed
     */
    public function getField($key)
    {
        return $this->getInboundDataArray()[$key] ?? null;
    }
}
