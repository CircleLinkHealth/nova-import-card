<?php namespace App\Importer\Models\ImportedItems;

use App\Importer\Models\ItemLogs\ProblemLog;
use Illuminate\Database\Eloquent\Model;

class ProblemImport extends Model {

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(ProblemLog::class);
    }

    /**
     * Gets the kind of code for a problem (ICD 9, ICD10, Snomed)
     *
     * @return bool|string
     */
    public function getCodeType()
    {
        /*
         * ICD-9 Check
         */
        if ((str_contains(strtolower($this->code_system_name), 'icd')
                && str_contains(strtolower($this->code_system_name), '9'))
            || $this->code_system == '2.16.840.1.113883.6.103'
        ) {
            return 'icd_9_code';
        }

        /*
       * ICD-10 Check
       */
        if ((str_contains(strtolower($this->code_system_name), 'icd')
                && str_contains(strtolower($this->code_system_name), '10'))
            || in_array($this->code_system, [
                '2.16.840.1.113883.6.3',
                '2.16.840.1.113883.6.4',
            ])
        ) {
            return 'icd_10_code';
        }


        /*
             * SNOMED Check
             */
        if (str_contains(strtolower($this->code_system_name), 'snomed')
            || $this->code_system == '2.16.840.1.113883.6.96'
        ) {
            return 'snomed_code';
        }

        return false;
    }
}
