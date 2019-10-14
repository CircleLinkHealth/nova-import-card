<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\ImportPracticeStaffCsv;

use CircleLinkHealth\Customer\Entities\Practice;
use Laravel\Nova\Card;

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
        //todo: remove from constructor
        $practices = Practice::
//        whereIn('id', auth()->user()->viewableProgramIds())
//                             ->
                             activeBillable()
                                 ->pluck('id', 'display_name')
                                 ->toArray();

        parent::__construct();

        $this->withMeta([
            'fields' => [
                //add select
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
