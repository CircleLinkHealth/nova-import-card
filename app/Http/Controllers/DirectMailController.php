<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\DirectMailMessage;

class DirectMailController extends Controller
{
    public function show($directMailId)
    {
        $dm = DirectMailMessage::query()
            ->with('ccdas', 'media')
            ->findOrFail($directMailId);

        return view('direct-mail.show-message')
            ->with('dm', $dm);
    }
}
