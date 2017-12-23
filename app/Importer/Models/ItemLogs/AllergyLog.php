<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\AllergyImport;
use App\Traits\Relationships\BelongsToCcda;
use App\Traits\Relationships\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Importer\Models\ItemLogs\AllergyLog
 *
 * @property int $id
 * @property string|null $medical_record_type
 * @property int|null $medical_record_id
 * @property int|null $vendor_id
 * @property string|null $start
 * @property string|null $end
 * @property string|null $status
 * @property string|null $allergen_name
 * @property int $import
 * @property int $invalid
 * @property int $edited
 * @property string|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\MedicalRecords\Ccda $ccda
 * @property-read \App\Importer\Models\ImportedItems\AllergyImport $importedItem
 * @property-read \App\Models\CCD\CcdVendor|null $vendor
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereAllergenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereEdited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereVendorId($value)
 * @mixin \Eloquent
 */
class AllergyLog extends \App\BaseModel implements ItemLog
{
    use BelongsToCcda,
        BelongsToVendor;

    protected $table = 'ccd_allergy_logs';

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'vendor_id',
        'start',
        'end',
        'status',
        'allergen_name',
        'import',
        'invalid',
        'edited',
    ];

    public function importedItem()
    {
        return $this->hasOne(AllergyImport::class);
    }
}
