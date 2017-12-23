<?php

namespace App\Models;

use App\CarePlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Pdf
 *
 * @property int $id
 * @property string $pdfable_type
 * @property int $pdfable_id
 * @property string $filename
 * @property int|null $uploaded_by
 * @property string $file
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $pdfable
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Pdf onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf wherePdfableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf wherePdfableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereUploadedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Pdf withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Pdf withoutTrashed()
 * @mixin \Eloquent
 */
class Pdf extends \App\BaseModel
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
