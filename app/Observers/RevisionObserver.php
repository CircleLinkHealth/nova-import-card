<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 8/7/18
 * Time: 1:51 AM
 */

namespace App\Observers;

use Venturecraft\Revisionable\Revision;

class RevisionObserver
{
    public function saving(Revision $revision)
    {
        $revision->is_phi = $this->isPhi($revision);
    }

    private function isPhi(Revision $revision)
    {
        if (class_exists($class = $revision->revisionable_type)) {
            $revisedModel = app($class);

            return in_array($revision->key, $revisedModel->phi);
        }

        return false;
    }
}
