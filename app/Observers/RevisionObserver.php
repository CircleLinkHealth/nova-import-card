<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Revisionable\Entities\Revision;

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
