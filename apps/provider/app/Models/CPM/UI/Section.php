<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM\UI;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/29/16
 * Time: 2:47 PM.
 */
class Section
{
    public $items = [];
    public $miscs = [];
    public $name;
    public $patientItemIds  = [];
    public $patientItems    = [];
    public $patientMiscs    = [];
    public $patientMiscsIds = [];
    public $title;

    public function __construct($name = null, $title = null, $items = [], $misc = [], $patientItemIds = [], $patientMiscsIds = [], $patientItems = [], $patientMiscs = [])
    {
        $this->items           = $items;
        $this->patientItemIds  = $patientItemIds;
        $this->patientItems    = $patientItems;
        $this->name            = $name;
        $this->title           = $title;
        $this->miscs           = $misc;
        $this->patientMiscsIds = $patientMiscsIds;
        $this->patientMiscs    = $patientMiscs;
    }
}
