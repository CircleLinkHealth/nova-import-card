<?php
namespace App;

use Franzose\ClosureTable\Models\ClosureTable;

class LocationClosure extends ClosureTable implements LocationClosureInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'location_closure';
}
