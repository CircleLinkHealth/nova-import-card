<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Revision Model
    |--------------------------------------------------------------------------
    */
    'model' => \CircleLinkHealth\Revisionable\Entities\Revision::class,

    /*
    |--------------------------------------------------------------------------
    | Queue to dispatch revisionable jobs to
    |--------------------------------------------------------------------------
    */
    'queue' => env('REVISIONABLE_QUEUE', 'revisionable'),
];
