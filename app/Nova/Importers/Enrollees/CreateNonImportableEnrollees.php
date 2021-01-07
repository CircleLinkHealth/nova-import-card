<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;


use CircleLinkHealth\SharedModels\Entities\Enrollee;

class CreateNonImportableEnrollees extends EnrolleeImportingAction
{

    protected function fetchEnrollee()
    {
        // TODO: Implement fetchEnrollee() method.
    }

    protected function shouldPerformAction(Enrollee $enrollee, array $row): bool
    {
        // TODO: Implement shouldPerformAction() method.
    }

    protected function performAction(Enrollee $enrollee)
    {
        // TODO: Implement performAction() method.
    }

    protected function validateRow(array $row): bool
    {
        // TODO: Implement validateRow() method.
    }
}