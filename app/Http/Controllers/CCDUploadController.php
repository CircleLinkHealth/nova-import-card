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
     * @throws \Exception
     */
	public function uploadRawFiles(Request $request)
    {
        $uploaded = [];

        if (!empty($_FILES['file']['name'][0])) {
            foreach ($_FILES['file']['name'] as $position => $name) {
                $xml = file_get_contents($_FILES['file']['tmp_name'][$position]);

                if($request->session()->has('blogId')) {
                    $blogId = $request->session()->get('blogId');
                }
                else {
                    throw new \Exception('Blog id not found,', 400);
                }

                $user = $this->repo->createRandomUser($blogId);

                $newCCD = new XmlCCD();
                $newCCD->ccd = $xml;
                $newCCD->user_id = $user->ID;
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
    public function create($id)
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

        foreach ($receivedFiles as $file) {
            $parsedCCD = new ParsedCCD();
            $parsedCCD->ccd = json_encode($file->ccd);
            $parsedCCD->user_id = $file->userId;
            $parsedCCD->save();
        }

        return response()->json('Files received and processed successfully', 200);
    }

}
