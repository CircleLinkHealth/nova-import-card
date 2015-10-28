<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\ParsedCCD;
use App\XmlCCD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class CCDUploadController extends Controller {

    /**
     * @param Request $request
     * @return string
     */
	public function uploadRawFiles(Request $request)
    {
        $uploaded = [];
        if (!empty($_FILES['file']['name'][0])) {
            foreach ($_FILES['file']['name'] as $position => $name) {
                $xml = file_get_contents($_FILES['file']['tmp_name'][$position]);

                $newCCD = new XmlCCD();
                $newCCD->ccd = $xml;
                $newCCD->save();

                array_push($uploaded, $xml);
            }
        }
        return json_encode($uploaded);
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

        foreach ($receivedFiles as $file) {
            $parsedCCD = new ParsedCCD();
            $parsedCCD->ccd = json_encode($file);
            $parsedCCD->save();
        }

        return response('Files received and processed successfully', 200);
    }

}
