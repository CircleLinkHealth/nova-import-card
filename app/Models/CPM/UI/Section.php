<?php namespace App\Models\CPM\UI;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/29/16
 * Time: 2:47 PM
 */
class Section
{
    public $name = null;
    public $title = null;
    public $items = [];
    public $patientItemIds = [];
    public $patientItems = [];
    public $miscs = [];
    public $patientMiscsIds = [];
    public $patientMiscs = [];

    public function __construct($name = null, $title = null, $items = [], $misc = [], $patientItemIds = [], $patientMiscsIds = [], $patientItems = [], $patientMiscs = [])
    {
        $this->items = $items;
        $this->patientItemIds = $patientItemIds;
        $this->patientItems = $patientItems;
        $this->name = $name;
        $this->title = $title;
        $this->miscs = $misc;
        $this->patientMiscsIds = $patientMiscsIds;
        $this->patientMiscs = $patientMiscs;
    }
}
