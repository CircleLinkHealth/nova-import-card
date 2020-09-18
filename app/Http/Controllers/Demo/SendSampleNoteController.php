<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Demo;

use App\Contracts\Efax;
use App\Http\Controllers\Controller;
use CircleLinkHealth\SharedModels\Entities\Note;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class SendSampleNoteController extends Controller
{
    private $fax;

    public function __construct(Efax $fax)
    {
        $this->fax = $fax;
    }

    public function makePdf(Request $request)
    {
        $demo = Practice::whereName('demo')->firstOrFail();

        //pick a random demo patient
        $patient = User::ofType('participant')->ofPractice($demo->id)->firstOrFail();

        //pick a random demo provider
        $provider = User::ofType('provider')->ofPractice($demo->id)->firstOrFail();

        $sampleNote                       = new Note();
        $sampleNote->patient_id           = $patient->id;
        $sampleNote->author_id            = $provider->id;
        $sampleNote->logger_id            = $provider->id;
        $sampleNote->body                 = $request->input('note_body');
        $sampleNote->isTCM                = false;
        $sampleNote->type                 = 'Test Note';
        $sampleNote->did_medication_recon = false;
        $sampleNote->performed_at         = Carbon::now();

        $pdf = $sampleNote->toPdf($request->input('scale', null));

        $practices = Practice::active()
            ->orderBy('display_name')
            ->get();

        return view('admin.demo.note.review', [
            'filePath'  => $pdf,
            'practices' => $practices,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function sendNoteViaEFax(Request $request)
    {
        $path = $request->input('filePath');

        if ( ! file_exists($path)) {
            throw new \Exception("Could find file at: `${path}`", 404);
        }

//        $practice = Practice::findOrFail($request->input('practice_id'));
//
//        if (!$practice->fax) {
//            throw new \Exception("Practice has no fax.", 400);
//        }
//
//        $this->fax->send($practice->fax, $path);

        $fax = formatPhoneNumberE164($request->input('fax'));

        if ( ! $fax) {
            throw new \Exception('Invalid fax number.', 400);
        }

        $result = $this->fax->send($fax, $path);

        dd($result);
    }

    public function showMakeNoteForm()
    {
        return view('admin.demo.note.create');
    }
}
