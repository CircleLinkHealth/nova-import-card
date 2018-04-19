<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/19/18
 * Time: 8:03 PM
 */

namespace App\Observers;


use App\EligibilityBatch;

class EligibilityBatchObserver
{
    public function creating(EligibilityBatch $eligibilityBatch) {
        if (!$eligibilityBatch->stats) {
            $eligibilityBatch->stats = [
                'eligible'   => 0,
                'ineligible' => 0,
                'errors'     => 0,
                'duplicates' => 0,
            ];
        }
    }
}