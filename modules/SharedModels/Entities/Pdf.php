<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\SharedModels\Entities\Pdf.
 *
 * @property int                                           $id
 * @property string                                        $pdfable_type
 * @property int                                           $pdfable_id
 * @property string                                        $filename
 * @property int|null                                      $uploaded_by
 * @property string                                        $file
 * @property \Carbon\Carbon|null                           $created_at
 * @property \Carbon\Carbon|null                           $updated_at
 * @property string|null                                   $deleted_at
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $pdfable
 * @method static                                        bool|null forceDelete()
 * @method static                                        \Illuminate\Database\Query\Builder|\App\Models\Pdf onlyTrashed()
 * @method static                                        bool|null restore()
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereCreatedAt($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereDeletedAt($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereFile($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereFilename($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereId($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf wherePdfableId($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf wherePdfableType($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereUpdatedAt($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereUploadedBy($value)
 * @method static                                        \Illuminate\Database\Query\Builder|\App\Models\Pdf withTrashed()
 * @method static                                        \Illuminate\Database\Query\Builder|\App\Models\Pdf withoutTrashed()
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf newModelQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf newQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf query()
 * @property int|null                                                                                    $revision_history_count
 */
class Pdf extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'uploaded_by',
        'pdfable_type',
        'pdfable_id',
        'filename',
        'file',
    ];

    protected $hidden = ['file'];

    /**
     * Get all of the owning pdfable models.
     */
    public function pdfable()
    {
        return $this->morphTo();
    }
}
