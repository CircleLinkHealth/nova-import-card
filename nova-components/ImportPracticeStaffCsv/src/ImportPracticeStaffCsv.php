<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ImportPracticeStaffCsv;

use Laravel\Nova\Card;

class ImportPracticeStaffCsv extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/2';

    public function __construct($resource, $practices)
    {
        parent::__construct();

        $this->withMeta([
            'fields' => [
            ],
            'practices'     => $practices,
            'resourceLabel' => $resource::label(),
            'resource'      => $resource::uriKey(),
        ]);
    }

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'import-practice-staff-csv';
    }
}
