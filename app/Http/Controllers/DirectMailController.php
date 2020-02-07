<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Contracts\DirectMail;
use App\DirectMailMessage;

class DirectMailController extends Controller
{
    public function checkInbox(DirectMail $directMail)
    {
        return $directMail->receive();
    }

    public function show($directMailId)
    {
        $dm = DirectMailMessage::query()
            ->with('ccdas', 'media')
            ->findOrFail($directMailId);

        return view('direct-mail.show-message')
            ->with('dm', $dm);
    }
}
