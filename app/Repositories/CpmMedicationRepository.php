<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CCD\Medication;

class CpmMedicationRepository
{
    public function model()
    {
        return app(Medication::class);
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function search($terms)
    {
        $query = $this->model();
        if (is_array($terms)) {
            $i = 0;
            foreach ($terms as $term) {
                if ($i == 0) {
                    $query = $query->where('name', 'LIKE', '%' . $term . '%');
                } else {
                    $query = $query->orWhere('name', 'LIKE', '%' . $term . '%');
                }
                $i++;
            }
        } else {
            $query = $query->orWhere('name', 'LIKE', '%' . $terms . '%');
        }
        return $query->groupBy('name')->get();
    }

    public function patientMedication($userId)
    {
        return $this->model()->where([
            'patient_id' => $userId,
        ])->paginate();
    }

    public function patientMedicationsList($userId)
    {
        return $this
            ->model()
            ->where([
                'patient_id' => $userId,
            ])
            ->select(['name', 'sig'])
            ->get();
    }

    public function exists($id)
    {
        return ! ! $this->model()->find($id);
    }

    public function addMedicationToPatient(Medication $medication)
    {
        $medication->save();
        return $medication;
    }

    public function removeMedicationFromPatient($medicationId, $userId)
    {
        $this->model()->where([
            'patient_id' => $userId,
            'id'         => $medicationId,
        ])->delete();

        return [
            'message' => 'success',
        ];
    }

    public function editPatientMedication(Medication $medication)
    {
        if ( ! $medication->id) {
            throw new Exception('"id" is important');
        } else {
            $medications = $this->model()->where(['id' => $medication->id]);
            $medications->update([
                'name'                => $medication->name,
                'sig'                 => $medication->sig,
                'medication_group_id' => $medication->medication_group_id,
            ]);
            return $medications->first();
        }
    }
}