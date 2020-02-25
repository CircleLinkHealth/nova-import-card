<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\ImportRoutine;

/**
 * App\CLH\CCD\ImportRoutine\CcdImportStrategies.
 *
 * @property int            $id
 * @property int            $ccd_import_routine_id
 * @property int            $importer_section_id
 * @property int            $validator_id
 * @property int            $parser_id
 * @property int            $storage_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereCcdImportRoutineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereImporterSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereParserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereStorageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereValidatorId($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies query()
 *
 * @property int|null $revision_history_count
 */
class CcdImportStrategies extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];
    protected $table   = 'ccd_import_routines_strategies';
}
