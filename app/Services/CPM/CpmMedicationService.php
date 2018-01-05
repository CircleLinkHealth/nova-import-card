<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:20 PM
 */

namespace App\Services\CPM;

use App\User;
use App\Repositories\CpmMedicationRepository;

class CpmMedicationService
{
    private $medicationRepo;

    public function __construct(CpmMedicationRepository $medicationRepo) {
        $this->medicationRepo = $medicationRepo;
    }

    public function repo() {
        return $this->medicationRepo;
    }

    public function medications() {
        return $this->repo()->model()->paginate();
    }

    public function search($terms) {
        return $this->repo()->search($terms);
    }
}
