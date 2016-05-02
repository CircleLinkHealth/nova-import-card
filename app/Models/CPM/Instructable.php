<?php

namespace App\Models\CPM;


trait Instructable
{
    /**
     * Use this relationships on Models that need to have Instructions
     *
     * @return mixed
     */
    public function instructions()
    {
        return $this->morphToMany(CpmInstruction::class, 'instructable');
    }
}