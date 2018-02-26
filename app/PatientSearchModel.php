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
class PatientSearchModel extends \App\BaseModel
{
    public static function create($data) {
        if ($data) {
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
        throw new \Exception('argument (data) not specified');
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
            $year = Carbon::now()->subYear($this->age)->format('Y');
            $query = $query->whereHas('patientInfo', function ($query) use ($year) {
               $query->where('birth_date', 'LIKE', $year . '%');
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
