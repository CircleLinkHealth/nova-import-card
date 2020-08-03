<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ClhImportCardExtended;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Laravel\Nova\Card;
use Laravel\Nova\Fields\Select;

class ClhImportCardExtended extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/2';

    public function __construct($resource, array $fields, $label = null)
    {
        parent::__construct();

        $this->withMeta([
            'fields'        => $fields,
            'resourceLabel' => $label ? ': '.$label : $resource::label(),
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
        return 'clh-import-card-extended';
    }

    public static function forUser(User $user, string $resource)
    {
        $practices = Practice::whereIn('id', auth()->user()->viewableProgramIds())
            ->activeBillable()
            ->pluck('display_name', 'id')
            ->toArray();

        return new ClhImportCardExtended($resource, [
            Select::make('practice')->options($practices)->withModel(Practice::class),
        ]);
    }
}
