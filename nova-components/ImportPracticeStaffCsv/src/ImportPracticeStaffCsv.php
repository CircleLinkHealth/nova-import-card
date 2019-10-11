<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\ImportPracticeStaffCsv;

use Laravel\Nova\Card;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Select;

class ImportPracticeStaffCsv extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/2';

    public function __construct($resource)
    {
        parent::__construct();
        $this->withMeta([
            'fields' => [
                new File('File'),
                new Select('practice_id')
            ],
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
