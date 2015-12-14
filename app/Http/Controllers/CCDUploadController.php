<?php namespace App\Http\Controllers;

use App\CLH\CCD\Importer\Parsers\CCDImportParser;
use App\CLH\CCD\Parser\CCDParser;
use App\CLH\Repositories\CCDImporterRepository;
use App\CLH\Repositories\WpUserRepository;
use App\Http\Requests;
use App\ParsedCCD;
use App\WpUser;
use App\XmlCCD;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

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
        $uploaded = [];
        $duplicates = [];

        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {

                if (empty($file->getPathName())) continue;

                $xml = file_get_contents($file->getPathName());

                if($request->session()->has('blogId')) {
                    $blogId = $request->session()->get('blogId');
                }
                else {
                    throw new \Exception('Blog id not found,', 400);
                }

                $parser = new CCDParser($xml);
                $fullName = $parser->getFullName();
                $dob = $parser->getDob();

                if (XmlCCD::wherePatientName($fullName)->wherePatientDob($dob)->exists()) {
                    array_push($duplicates, $file->getClientOriginalName());
                    continue;
                }



                $user = $this->repo->createRandomUser($blogId);

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
        return response()->json($uploaded, 200);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('CCDUploader.uploader');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function storeParsedFiles(Request $request)
    {
        $receivedFiles = json_decode($request->getContent());

        if (empty($receivedFiles)) return response()->json('Transporting CCDs to the server has failed.', 500);

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

            $importParser = (new CCDImportParser($blogId, $parsedCCD))->parse();

            $userRepo = new WpUserRepository();
            $wpUser = WpUser::find($parsedCCD->user_id);

            $userRepo->updateUserConfig($wpUser, new ParameterBag($importParser->userConfig));

            $userRepo->saveOrUpdateUserMeta($wpUser, new ParameterBag($importParser->userMeta));
        }

        return response()->json('Files received and processed successfully', 200);
    }

}
