<?php namespace App;

use App\User;
use App\Patient;
use Carbon\Carbon;

/**
 * App\PatientSearchModel
 * 
 * @description created for searching through users for the patient-listing view
 *
 * @property string $name
 * @property string $provider
 * @property string $ccmStatus
 * @property string $careplanStatus
 * @property string|null $dob
 * @property string|null $phone
 * @property int $age
 * @property string|null $registeredOn
 * @property string|null $lastReading
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 */
class PatientSearchModel
{
    public static function create($data) {
        $data = $data ?? new \stdClass();
        $model = new PatientSearchModel();
        $model->name = isset($data['name']) ? $data['name'] : null;
        $model->provider = isset($data['provider']) ? $data['provider'] : null;
        $model->careplanStatus = isset($data['careplanStatus']) ? $data['careplanStatus'] : null;
        $model->dob = isset($data['dob']) ? $data['dob'] : null;
        $model->phone = isset($data['phone']) ? $data['phone'] : null;
        $model->age = isset($data['age']) ? $data['age'] : null;
        $model->registeredOn = isset($data['registeredOn']) ? $data['registeredOn'] : null;
        $model->lastReading = isset($data['lastReading']) ? $data['lastReading'] : null;
        return $model;
    }

    public function users() {
        $query = User::query();
        if ($this->name) {
            $query = $query->where('display_name', 'LIKE', '%' . $this->name . '%');
        }
        
        if ($this->provider) {
            $query = $query->whereHas('billingProvider', function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('display_name', $this->provider);
                });
            });
        }
        
        if ($this->careplanStatus) {
            $query = $query->whereHas('carePlan', function ($query) {
               $query->where('status', $this->careplanStatus)->orWhere('status', 'LIKE', '%\"status\":\"' . $this->careplanStatus . '\"%');
            });
        }
        
        if ($this->dob) {
            $query = $query->whereHas('patientInfo', function ($query) {
               $query->where('birth_date', $this->dob);
            });
        }
        
        if ($this->phone) {
            $query = $query->whereHas('phoneNumbers', function ($query) {
               $query->where('number', $this->phone);
            });
        }
        
        if ($this->age) {
            $date = Carbon::now()->subYear($this->age)->format('Y');
            $query = $query->whereHas('patientInfo', function ($query) use ($date) {
               $query->where('birth_date', 'LIKE', $date . '%');
            });
        }
        
        if ($this->registeredOn) {
            $query = $query->where('created_at', 'LIKE', $this->registeredOn . ' %');
        }
        
        if ($this->lastReading) {
            $query = $query->whereHas('lastObservation', function ($query) {
                $query->where('obs_date', 'LIKE', $this->lastReading . '%');
             });
        }

        return $query;
    }

    public function results() {
        $users = $this->users()->whereHas('patientInfo')->paginate();
        $users->getCollection()->transform(function ($user) {
            $user = optional($user)->safe();
            return $user;
        });
        return $users;;
    }
}
