<?php

namespace CircleLinkHealth\Eligibility\CcdaImporter\Settings;

use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ReplaceFieldsFromSupplementaryData;

class Hooks
{
    const LISTENERS = [
        ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME => ReplaceFieldsFromSupplementaryData::class,
    ];
}