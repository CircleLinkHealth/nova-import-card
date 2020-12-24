<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use CircleLinkHealth\Core\Contracts\DirectMail;
use CircleLinkHealth\SharedModels\Entities\DirectMailMessage;
use CircleLinkHealth\SharedModels\Services\PhiMail\CheckResult;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DirectMailController extends Controller
{
    public function checkInbox(DirectMail $directMail)
    {
        return $directMail->receive();
    }

    public function send(Request $request, DirectMail $directMail)
    {
        $response = $directMail->send(
            $request->input('dm_to_address'),
            null,
            null,
            null,
            null,
            $request->input('dm_body'),
            $request->input('dm_subject'),
            $request->input('dm_from_address')
        );

        if (1 !== count($response)) {
            return redirect()->back()->with('message', 'CPM could not send your message. Please make sure the DM addresses you are using are valid. If the issue persists, please notify Dev Team/');
        }

        /** @var CheckResult $response */
        $checkResult = $response[0];

        if ((bool) $checkResult->succeeded) {
            $message = 'Message sent.';
        } else {
            $message = 'Message NOT sent. Error: '.$checkResult->errorText;
        }

        $message .= PHP_EOL." Recipient: {$checkResult->recipient}. HISP Msg ID: {$checkResult->messageId}.";

        if ($dm = DirectMailMessage::where('message_id', $checkResult->messageId)->first()) {
            $message .= PHP_EOL.link_to_route('direct-mail.show', 'View Message', [$dm->id]);
        }

        return redirect()->back()->with('message', $message);
    }

    public function show($directMailId)
    {
        if ('new' === $directMailId) {
            return view('cpm-admin::direct-mail.show-message');
        }
        $dm = DirectMailMessage::query()
            ->with('ccdas', 'media')
            ->findOrFail($directMailId);

        $linksToPatients = $dm->ccdas->pluck('patient_id')->filter()->values()->map(function ($patientId) {
            return link_to_route('patient.note.index', "Patient ID: $patientId", [$patientId]);
        });

        return view('cpm-admin::direct-mail.show-message')
            ->with('dm', $dm)
            ->with('links', $linksToPatients);
    }
}
