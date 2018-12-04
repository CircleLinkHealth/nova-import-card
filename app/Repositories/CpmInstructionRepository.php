<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/07/2017
 * Time: 12:32 PM
 */

namespace App\Repositories;

use App\Models\CPM\CpmInstruction;

class CpmInstructionRepository
{
    public function model()
    {
        return app(CpmInstruction::class);
    }
}
