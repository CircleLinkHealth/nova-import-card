<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */
namespace CircleLinkHealth\Core\Entities;


use CircleLinkHealth\Core\Traits\ProtectsPhi;
use Illuminate\Database\Eloquent\Model;

class SqlViewModel extends Model
{
    use ProtectsPhi;


    public $phi = [];
}