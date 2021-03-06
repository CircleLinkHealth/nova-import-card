<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Contracts;

use Illuminate\Http\Request;

interface SignedStorageUrlController
{
    /**
     * Create a new signed URL.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request);
}
