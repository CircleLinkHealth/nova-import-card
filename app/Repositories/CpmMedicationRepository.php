<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Models\CCD\Medication;

class CpmMedicationRepository
{
    public function addMedicationToPatient(Medication $medication)
    {
        $medication->save();

        return $medication;
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function editPatientMedication(Medication $medication)
    {
        if (!$medication->id) {
            throw new Exception('"id" is important');
        }
        $medications = $this->model()->where(['id' => $medication->id]);
        $medications->update([
            'name'                => $medication->name,
            'sig'                 => $medication->sig,
            'medication_group_id' => $medication->medication_group_id,
        ]);

        return $medications->first();
    }

    public function exists($id)
    {
        return (bool) $this->model()->find($id);
    }

    public function model()
    {
        return app(Medication::class);
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

    public function search($terms)
    {
        $query = $this->model();
        if (is_array($terms)) {
            $i = 0;
            foreach ($terms as $term) {
                if (0 == $i) {
                    $query = $query->where('name', 'LIKE', '%'.$term.'%');
                } else {
                    $query = $query->orWhere('name', 'LIKE', '%'.$term.'%');
                }
                ++$i;
            }
        } else {
            $query = $query->orWhere('name', 'LIKE', '%'.$terms.'%');
        }

        return $query->groupBy('name')->get();
    }
}
