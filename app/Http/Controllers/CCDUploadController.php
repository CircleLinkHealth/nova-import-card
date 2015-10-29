<?php namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\ParsedCCD;
use App\XmlCCD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

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
     */
	public function uploadRawFiles(Request $request)
    {
        $uploaded = [];
        if (!empty($_FILES['file']['name'][0])) {
            foreach ($_FILES['file']['name'] as $position => $name) {
                $xml = file_get_contents($_FILES['file']['tmp_name'][$position]);

                $user = $this->repo->createRandomUser();

                $newCCD = new XmlCCD();
                $newCCD->ccd = $xml;
                $newCCD->user_id = $user->ID;
                $newCCD->save();

                array_push($uploaded, $xml);
            }
        }
        return response()->json($uploaded, 200, [
            //These could be useless @todo erase them in not needed
//            'Access-Control-Allow-Origin:' => 'http://localcrisfield.careplanmanager.com',
//            'Access-Control-Allow-Credentials:' => ['http://localcrisfield.careplanmanager.com'],
        ]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('CCDUploader.uploader')->render();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function storeParsedFiles(Request $request)
    {
        $receivedFiles = json_decode($request->getContent());

        foreach ($receivedFiles as $file) {
            $parsedCCD = new ParsedCCD();
            $parsedCCD->ccd = json_encode($file);
            $parsedCCD->save();
        }

        return response()->json('Files received and processed successfully', 200, [
            //These could be useless @todo erase them in not needed
//            'Access-Control-Allow-Origin:' => 'http://localcrisfield.careplanmanager.com',
//            'Access-Control-Allow-Credentials:' => ['http://localcrisfield.careplanmanager.com'],
        ]);
    }

}
