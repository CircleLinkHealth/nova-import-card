<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Filters\NoteFilters;
use CircleLinkHealth\SharedModels\Entities\Note;
use Carbon\Carbon;

class NoteRepository
{
    public function add(Note $note)
    {
        if ($note && $note->patient_id && $note->author_id && $note->body && $note->type) {
            $note->performed_at = Carbon::now();
            $note->save();

            return $note;
        }
        if ( ! $note) {
            throw new \Exception('invalid $note');
        }
        if ( ! $note->patient_id) {
            throw new \Exception('invalid $note->patient_id');
        }
        if ( ! $note->author_id) {
            throw new \Exception('invalid $note->author_id');
        }
        if ( ! $note->body) {
            throw new \Exception('invalid $note->body');
        }
        if ( ! $note->type) {
            throw new \Exception('invalid $note->type');
        }
        throw new \Exception('invalid parameters');
    }

    public function addOrEdit(Note $note)
    {
        if ($note && $note->patient_id && $note->author_id && $note->body && $note->type) {
            $savedNote = $this->model()->firstOrCreate([
                'patient_id' => $note->patient_id,
                'author_id'  => $note->author_id,
                'type'       => $note->type,
            ]);
            $savedNote->update([
                'summary'              => $note->summary,
                'body'                 => $note->body,
                'isTCM'                => $note->isTCM,
                'logger_id'            => $note->logger_id,
                'did_medication_recon' => $note->did_medication_recon,
                'performed_at'         => $note->performed_at,
            ]);
            $savedNote->save();

            return $savedNote;
        }

        return $note;
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function edit(Note $note)
    {
        if ($note && $note->id && $this->exists($note->id)) {
            $notes = $this->model()->where(['id' => $note->id]);
            $notes->update([
                'body'                 => $note->body,
                'summary'              => $note->summary,
                'isTCM'                => $note->isTCM ?? 0,
                'did_medication_recon' => $note->did_medication_recon,
            ]);

            return $notes->first();
        }

        return null;
    }

    public function exists($id)
    {
        return (bool) $this->model()->find($id);
    }

    public function model()
    {
        return app(Note::class);
    }

    public function patientNotes($userId, NoteFilters $filters)
    {
        $query = $this->model()->where([
            'patient_id' => $userId,
        ])->filter($filters);

        return $query->paginate($filters->filters()['rows'] ?? 15);
    }
}
