<?php namespace App\Http\Controllers;

use App\CLH\CCD\Importer\Parsers\CCDImporter;
use App\CLH\CCD\Parser\CCDParser;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\ParsedCCD;
use App\XmlCCD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CCDUploadController extends Controller {

    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Receives XML files, saves them in DB, and returns them JSON Encoded
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
	public function uploadRawFiles(Request $request)
    {
        //CCDs that already exist in XML_CCDs table
        $duplicates = [];

        //CCDs just added to XML_CCDs table
        $uploaded = [];

        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                if (empty($file)) {
                    Log::error('It seems like this file did not upload. Here is what I have for $file in '
                        . __METHOD__ . ' ' . __LINE__ . '==>' . $file);
                    continue;
                }

                $xml = file_get_contents($file);

                $viewableProgramIds = auth()->user()->viewableProgramIds();

                if (empty($viewableProgramIds)) {
                    abort(404, 'You do not have access to any Programs');
                }


                /**
                 * Checking for duplicates
                 */
                $parser = new CCDParser($xml);
                $fullName = $parser->getFullName();
                $dob = $parser->getDob();

                $email = empty($parser->getEmail())
                    ? ''
                    : $parser->getEmail();

                if (XmlCCD::wherePatientName($fullName)->wherePatientDob($dob)->exists()) {
                    array_push($duplicates, [
                        'blogId' => $blogId,
                        'ccd' => $xml,
                        'fullName' => $fullName,
                        'dob' => $dob,
                        'fileName' => $file->getClientOriginalName()
                    ]);
                    continue;
                }

                $user = $this->repo->createRandomUser($blogId, $email, $fullName);

                $newCCD = new XmlCCD();
                $newCCD->ccd = $xml;
                $newCCD->user_id = $user->ID;
                $newCCD->patient_name = (string) $fullName;
                $newCCD->patient_dob = (string) $dob;
                $newCCD->save();

                array_push($uploaded, [
                    'userId' => $user->ID,
                    'xml' => $xml,
                ]);
            }
        }

        if (empty($uploaded) && empty($duplicates)) {
            return response()->json('No CCDs were uploaded.', 400);
        }

        return response()->json(compact('uploaded', 'duplicates'), 200);
    }

    public function uploadDuplicateRawFiles(Request $request)
    {
        $uploaded = [];

        $receivedFiles = json_decode($request->getContent());

        if (empty($receivedFiles)) return;

        foreach ($receivedFiles as $file) {

            if (empty($file)) {
                Log::error('It seems like this file did not upload. Here is what I have for $file in '
                    . self::class . '@uploadDuplicateRawFiles() ==>' . $file);
                continue;
            }

            $parser = new CCDParser($file->xml);
            $fullName = $parser->getFullName();
            $email = empty($parser->getEmail())
                ? ''
                : $parser->getEmail();

            $user = $this->repo->createRandomUser($file->blogId, $email, $fullName);

            $newCCD = new XmlCCD();
            $newCCD->ccd = $file->xml;
            $newCCD->user_id = $user->ID;
            $newCCD->patient_name = (string) $file->fullName;
            $newCCD->patient_dob = (string) $file->dob;
            $newCCD->save();

            array_push($uploaded, [
                'userId' => $user->ID,
                'xml' => $file->xml,
            ]);
        }

        return response()->json(compact('uploaded'), 200);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $viewablePrograms = auth()->user()->programs()->get();

        if ($viewablePrograms->isEmpty()) {
            abort(404, 'You do not have access to any Programs');
        }

        return view('CCDUploader.uploader', compact('viewablePrograms'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function storeParsedFiles(Request $request)
    {
        $receivedFiles = json_decode($request->getContent());

        if (empty($receivedFiles)) return;

        foreach ($receivedFiles as $file) {
            $parsedCCD = new ParsedCCD();
            $parsedCCD->ccd = json_encode($file->ccd);
            $parsedCCD->user_id = $file->userId;
            $parsedCCD->save();

            if($request->session()->has('blogId')) {
                $blogId = $request->session()->get('blogId');
            } else {
                throw new \Exception('Blog ID missing.', 400);
            }

            /**
             * The CCDImporter calls any necessary Parsers
             */
            $importer = new CCDImporter($blogId, $parsedCCD);
            $importer->generateCarePlanFromCCD();
        }

        return response()->json('Files received and processed successfully', 200);
    }

}
