<?php namespace App\Models\CCD;

use App\Importer\Models\ItemLogs\AllergyLog;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CCD\Allergy
 *
 * @property int $id
 * @property int|null $allergy_import_id
 * @property int|null $ccda_id
 * @property int $patient_id
 * @property int|null $vendor_id
 * @property int|null $ccd_allergy_log_id
 * @property string|null $allergen_name
 * @property string|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Importer\Models\ItemLogs\AllergyLog $ccdLog
 * @property-read \App\User $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereAllergenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereAllergyImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereCcdAllergyLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereCcdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereVendorId($value)
 * @mixin \Eloquent
 */
class Allergy extends \App\BaseModel
{
    protected $fillable = [
        'ccda_id',
        'vendor_id',
        'patient_id',
        'ccd_allergy_log_id',
        'allergen_name',
    ];

    protected $table = 'ccd_allergies';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ccdLog()
    {
        return $this->belongsTo(AllergyLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
