<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\SharedModels\Entities\Allergy;

class CcdAllergyRepository
{
    public function addPatientAllergy($userId, $name)
    {
        $allergy                = new Allergy();
        $allergy->patient_id    = $userId;
        $allergy->allergen_name = $name;
        $allergy->save();

        return $allergy;
    }

    public function allergies()
    {
        return $this->model()->groupBy('allergen_name')->paginate(30);
    }

    public function count()
    {
        return $this->model()->select('allergen_name', DB::raw('count(*) as total'))->groupBy('allergen_name')->pluck('total')->count();
    }

    public function deletePatientAllergy($userId, $allergyId)
    {
        $this->model()->where(['patient_id' => $userId, 'id' => $allergyId])->delete();

        return [
            'message' => 'successful',
        ];
    }

    public function model()
    {
        return app(Allergy::class);
    }

    public function patientAllergies($userId)
    {
        return $this->model()->where(['patient_id' => $userId])->get();
    }

    public function patientAllergyExists($userId, $name)
    {
        return (bool) $this->model()->where(['patient_id' => $userId, 'allergen_name' => $name])->first();
    }

    public function patientIds($name)
    {
        return $this->model()->where(['allergen_name' => $name])->distinct(['patient_id'])->get(['patient_id']);
    }

    public function searchAllergies($terms)
    {
        $query = $this->model();
        if (is_array($terms)) {
            $i = 0;
            foreach ($terms as $term) {
                if (0 == $i) {
                    $query = $query->where('allergen_name', 'LIKE', '%'.$term.'%');
                } else {
                    $query = $query->orWhere('allergen_name', 'LIKE', '%'.$term.'%');
                }
                ++$i;
            }
        } else {
            $query = $query->orWhere('allergen_name', 'LIKE', '%'.$terms.'%');
        }

        return $query->groupBy('allergen_name')->get();
    }
}
