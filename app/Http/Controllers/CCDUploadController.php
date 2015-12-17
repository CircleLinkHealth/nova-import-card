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
        //CCDs that already exist in XML_CCDs table
        $duplicates = [];

        //CCDs just added to XML_CCDs table
        $uploaded = [];

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
        return response()->json(compact('uploaded', 'duplicates'), 200);
    }

    public function uploadDuplicateRawFiles(Request $request)
    {
        $uploaded = [];

        $receivedFiles = json_decode($request->getContent());

        /**
         * Returns empty because it's most probably called asynchronously from the uploader,
         * and we don't really want to trigger any errors.
         * @todo: there must be a much better way to do this on the JS side
         */
//        if (empty($receivedFiles)) return response()->json('Transporting duplicate CCDs to the server has failed.', 500);
        if (empty($receivedFiles)) return;

        foreach ($receivedFiles as $file) {

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
        return view('CCDUploader.uploader');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function storeParsedFiles(Request $request)
    {
        $receivedFiles = json_decode($request->getContent());

        /**
         * Returns empty because it's most probably called asynchronously from the uploader,
         * and we don't really want to trigger any errors.
         * @todo: there must be a much better way to do this on the JS side
         */
//        if (empty($receivedFiles)) return response()->json('Transporting parsed CCDs to the server has failed.', 500);
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

            $importParser = (new CCDImportParser($blogId, $parsedCCD))->parse();

            $userRepo = new WpUserRepository();
            $wpUser = WpUser::find($parsedCCD->user_id);

            $userRepo->updateUserConfig($wpUser, new ParameterBag($importParser->userConfig));

            $userRepo->saveOrUpdateUserMeta($wpUser, new ParameterBag($importParser->userMeta));
        }

        return response()->json('Files received and processed successfully', 200);
    }

}
