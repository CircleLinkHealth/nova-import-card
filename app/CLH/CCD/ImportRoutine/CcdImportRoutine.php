<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\ImportRoutine;

use App\Models\CCD\CcdVendor;

/**
 * App\CLH\CCD\ImportRoutine\CcdImportRoutine.
 *
 * @property int                                                                                       $id
 * @property string                                                                                    $name
 * @property string                                                                                    $description
 * @property \Carbon\Carbon                                                                            $created_at
 * @property \Carbon\Carbon                                                                            $updated_at
 * @property \App\CLH\CCD\ImportRoutine\CcdImportStrategies[]|\Illuminate\Database\Eloquent\Collection $strategies
 * @property \App\Models\CCD\CcdVendor[]|\Illuminate\Database\Eloquent\Collection                      $vendors
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine query()
 * @property int|null $revision_history_count
 * @property int|null $strategies_count
 * @property int|null $vendors_count
 */
class CcdImportRoutine extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];

    public function strategies()
    {
        return $this->hasMany(CcdImportStrategies::class, 'ccd_import_routine_id', 'id');
    }

    public function vendors()
    {
        return $this->hasMany(CcdVendor::class, 'ccd_import_routine_id', 'id');
    }
}
