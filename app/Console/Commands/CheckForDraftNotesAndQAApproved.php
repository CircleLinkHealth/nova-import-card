<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Console\Command;

class CheckForDraftNotesAndQAApproved extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for patients that have draft notes and are QA Approved. This probably means that note was drafted and could not be saved because Care Plan was not RN Approved.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:draft-notes-and-qa-approved {--force-notify}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $links = collect();
        Note::whereHas('patient.carePlan', function ($q) {
            $q->whereStatus(CarePlan::QA_APPROVED);
        })
            ->whereHas('patient.patientInfo', function ($q) {
                $q->whereCcmStatus(Patient::ENROLLED);
            })
            ->where('status', '=', Note::STATUS_DRAFT)
            ->each(function ($note) use ($links) {
                $route = route('patient.note.view', ['patientId' => $note->patient_id, 'noteId' => $note->id]);
                $links->push("Patient[$note->patient_id] - $route");
            });

        if ($links->isEmpty()) {
            return;
        }

        $str = "The following patients have draft notes and their care plans have not been approved by a care coach yet:\n";
        $str .= $links->join("\n");
        // $this->info($str);
        sendSlackMessage('#carecoach_ops', $str, $this->option('force-notify'));
    }
}
