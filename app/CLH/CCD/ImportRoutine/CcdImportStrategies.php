<?php namespace App\CLH\CCD\ImportRoutine;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CLH\CCD\ImportRoutine\CcdImportStrategies
 *
 * @property int $id
 * @property int $ccd_import_routine_id
 * @property int $importer_section_id
 * @property int $validator_id
 * @property int $parser_id
 * @property int $storage_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereCcdImportRoutineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereImporterSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereParserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereStorageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereValidatorId($value)
 * @mixin \Eloquent
 */
class CcdImportStrategies extends \App\BaseModel
{
    protected $table = 'ccd_import_routines_strategies';

    protected $guarded = [];
}
