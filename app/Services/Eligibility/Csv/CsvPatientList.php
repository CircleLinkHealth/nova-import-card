<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/30/18
 * Time: 1:28 PM
 */

namespace App\Services\Eligibility\Csv;

use App\Services\Eligibility\Csv\Validators\PatientList\NumberedFieldsValidator;
use App\Services\Eligibility\Csv\Validators\PatientList\PatientListValidator;
use App\Services\Eligibility\Csv\Validators\PatientList\SingleFieldsValidator;
use Illuminate\Support\Collection;

class CsvPatientList
{
    /**
     * @var Collection
     */
    private $patientList;

    /**
     * @var array
     */
    private $columnNames = [];

    /**
     * @var PatientListValidator $validator
     */
    private $validator;


    public function __construct(Collection $patientList)
    {
        $this->patientList = $patientList;
    }

    public function isValid()
    {
        return $this->validate() === true;
    }

    private function validate()
    {
        if (! $this->validator) {
            return null;
        }

        $this->validator->setColumnNames($this->getColumnNames());

        if ($this->validator->isValid()) {
            return true;
        }

        return false;
    }

    public function getColumnNames()
    {
        if ($this->columnNames) {
            return $this->columnNames;
        }

        $this->columnNames = $this->patientList->isNotEmpty()
            ? array_keys($this->patientList->first())
            : [];

        return $this->columnNames;
    }

    public function validationErrors()
    {
        return $this->validator->isValid()
            ? null
            : $this->validator->errors();
    }

    public function guessValidator()
    {
        $validators = [
            new SingleFieldsValidator(),
            new NumberedFieldsValidator(),
        ];

        foreach ($validators as $v) {
            $result = $this->setValidator($v)
                           ->validate();

            if ($result === true) {
                return true;
            }

            $this->validator = null;
        }

        return null;
    }

    /**
     * @param PatientListValidator $validator
     *
     * @return CsvPatientList
     */
    public function setValidator(PatientListValidator $validator)
    {
        $this->validator = $validator;

        return $this;
    }
}
