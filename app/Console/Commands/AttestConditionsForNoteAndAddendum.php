<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Note;
use Illuminate\Console\Command;

class AttestConditionsForNoteAndAddendum extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attest conditions to note and addendum';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:attest-problems {problemIds} {noteId} {addendumId?}';

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
     * @return int
     */
    public function handle()
    {
        $this->info('Preparing for problem attestation');

        if (empty($problemIds = explode(',', $this->argument('problemIds')))) {
            $this->error('No attested problem IDs have been inputted');

            return;
        }

        if ( ! $note = Note::with('call')->find($noteId = $this->argument('noteId'))) {
            $this->error("Note with ID: $noteId not found");

            return;
        }

        if ( ! $call = $note->call) {
            $this->error("Call not found for note with ID: $noteId");

            return;
        }

        $call->attachAttestedProblems($problemIds, $this->argument('addendumId'));
        $this->info('Conditions attested!');
    }
}
