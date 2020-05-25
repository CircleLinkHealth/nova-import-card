<?php


namespace App\SelfEnrollment\Domain;


use App\SelfEnrollment\Contracts\SelfEnrollable;
use Illuminate\Database\Eloquent\Builder;

class CreateUsersFromEnrollees extends AbstractSelfEnrollableModelIterator
{
    
    public function action(SelfEnrollable $enrollableModel): void
    {
        // TODO: Implement action() method.
    }
    
    public function query(): Builder
    {
        // TODO: Implement query() method.
    }
}