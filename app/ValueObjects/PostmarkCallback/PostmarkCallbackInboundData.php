<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;

use Illuminate\Support\Arr;

class PostmarkCallbackInboundData
{
    public function getInboundDataArray(array $inboundDataArray)
    {
        $inboundDataFormatted = [
            'from'      => $inboundDataArray['From'],
            'phone'     => $inboundDataArray['Phone'],
            'ptn'       => $inboundDataArray['Ptn'],
            'message'   => $inboundDataArray['Msg'],
            'primary'   => $inboundDataArray['Primary'],
            'messageId' => $inboundDataArray['Msg ID'],
            'isRecId'   => $inboundDataArray['IS Rec #'],
            'callerId'  => $inboundDataArray['Clr ID'],
            'taken'     => $inboundDataArray['Taken'],
        ];

        if (isset($inboundDataArray['Cancel/Withdraw Reason'])) {
            $inboundDataFormatted = Arr::add($inboundDataFormatted, 'cancelReason', $inboundDataArray['Cancel/Withdraw Reason']);
        }
        
        return $inboundDataFormatted;
    }
}
