<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Filters\NoteFilters;
use CircleLinkHealth\Customer\Services\NoteService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NoteController extends Controller
{
    /**
     * @var NoteService
     */
    protected $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function show($userId, NoteFilters $filters)
    {
        if ($userId) {
            return $this->noteService->patientNotes($userId, $filters);
        }

        return \response('"userId" is important');
    }

    public function store($userId, Request $request)
    {
        $body                 = $request->input('body');
        $author_id            = auth()->user()->id;
        $type                 = $request->input('type');
        $isTCM                = $request->input('isTCM') ?? 0;
        $did_medication_recon = $request->input('did_medication_recon') ?? 0;
        if ($userId && $author_id && ($body || 'Biometrics' == $type)) {
            return $this->noteService->add($userId, $author_id, $body, $type, $isTCM, $did_medication_recon);
        }

        return \response('"userId" and "body" and "author_id" are important');
    }

    public function update($userId, $id, Request $request)
    {
        $body                 = $request->input('body');
        $summary              = $request->input('summary');
        $author_id            = auth()->user()->id;
        $isTCM                = $request->input('isTCM') ?? 0;
        $type                 = $request->input('type');
        $did_medication_recon = $request->input('did_medication_recon') ?? 0;
        if ($userId && ($id || $type) && $author_id) {
            return $this->noteService->editPatientNote(
                $id,
                $userId,
                $author_id,
                $body,
                $isTCM,
                $did_medication_recon,
                $type,
                $summary
            );
        }

        return \response('"userId", "author_id" and "noteId" are is important');
    }
}
