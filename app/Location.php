<?php
namespace App;

use Franzose\ClosureTable\Models\Entity;

class Location extends Entity implements locationInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locations';

    /**
     * ClosureTable model instance.
     *
     * @var locationClosure
     */
    protected $closure = 'App\locationClosure';
}
