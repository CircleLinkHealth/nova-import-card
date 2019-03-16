<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Identifier\IdentificationStrategies;

class AuthorName extends BaseIdentificationStrategy
{
    public function identify()
    {
        if (empty($this->ccd->document->author->name->given[0])) {
            return false;
        }

        if (empty($this->ccd->document->author->name->family)) {
            return false;
        }

        $authorName = $this->ccd->document->author->name->given[0]
                      .' '
                      .$this->ccd->document->author->name->family;

        return empty($authorName)
            ? false
            : $authorName;
    }
}
