<?php

namespace App\Http\Controllers\Demo;

use App\Contracts\Efax;
use App\Http\Controllers\Controller;
use App\Note;
use App\Practice;
use Illuminate\Http\Request;

class SendSampleNoteController extends Controller
{
    private $fax;

    public function __construct(Efax $fax)
    {
        $this->fax = $fax;
    }

    public function showMakeNoteForm()
    {
        return view('admin.demo.note.create');
    }

    public function makePdf(Request $request)
    {
        $sampleNote       = factory(Note::class)->make();
        $sampleNote->body = $request->input('note_body');

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
     * @param Request $request
     *
     * @throws \Exception
     */
    public function sendNoteViaEFax(Request $request)
    {
        $path = $request->input('filePath');

        if ( ! file_exists($path)) {
            throw new \Exception("Could find file at: `$path`", 404);
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
            throw new \Exception("Invalid fax number.", 400);
        }

        $result = $this->fax->send($fax, $path);

        dd($result);
    }
}
