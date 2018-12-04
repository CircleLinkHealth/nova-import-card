<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/07/2017
 * Time: 12:32 PM
 */

namespace App\Repositories;

use App\Models\CPM\CpmBiometric;

class CpmBiometricRepository
{
    public function model()
    {
        return app(CpmBiometric::class);
    }

    public function biometrics()
    {
        return $this->model()->get();
    }
}
