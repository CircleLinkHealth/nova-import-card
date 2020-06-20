<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\FaxLog.
 *
 * @property int                             $id
 * @property string                          $vendor
 * @property int                             $fax_id
 * @property string|null                     $status
 * @property string|null                     $event_type
 * @property string                          $direction
 * @property array                           $response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog whereFaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FaxLog whereVendor($value)
 * @mixin \Eloquent
 */
class FaxLog extends Model
{
    protected $casts = [
        'response' => 'array',
    ];
    protected $fillable = [
        'fax_id',
        'event_type',
        'status',
        'direction',
        'response',
    ];
}
