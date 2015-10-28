<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\ParsedCCD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class CCDUploadController extends Controller {

	public function uploadRawFiles(Request $request)
    {
        $uploaded = [];
        if (!empty($_FILES['file']['name'][0])) {
            foreach ($_FILES['file']['name'] as $position => $name) {
                array_push($uploaded, file_get_contents($_FILES['file']['tmp_name'][$position]));
            }
        }
        return json_encode($uploaded);
    }

    public function create()
    {
        return view('CCDUploader.uploader');
    }

    public function storeParsedFiles(Request $request)
    {
        $receivedFiles = json_decode($request->getContent());

        foreach ($receivedFiles as $file) {
            $parsedCCD = new ParsedCCD();
            $parsedCCD->parsed_ccd = json_encode($file);
            $parsedCCD->save();
        }

        return response('Files received and processed successfully', 200);
    }

}
