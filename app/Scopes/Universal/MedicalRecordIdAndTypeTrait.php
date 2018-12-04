<?php namespace App\Scopes\Universal;

use App\Models\MedicalRecords\Ccda;

/**
 * Class DateScopesTrait
 *
 * Applies to Models.
 * Put all date specific scopes that we can use with multiple Models here.
 */
trait MedicalRecordIdAndTypeTrait
{
    /**
     * Scope a query by a Medical Record Id and Type
     *
     * @param $builder
     * @param $medicalRecordId
     * @param $medicalRecordType
     */
    public function scopeWithMedicalRecord(
        $builder,
        $id,
        $type = Ccda::class
    ) {
        $builder->where(function ($q) use (
            $id,
            $type
        ) {
            $q->where('medical_record_type', '=', $type)
                ->where('medical_record_id', '=', $id);
        });
    }
}
