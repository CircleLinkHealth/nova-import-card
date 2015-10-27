<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class CCDUploadController extends Controller {

	public function uploadFile(Request $request)
    {
//        return json_encode(file_get_contents($_FILES['file']['tmp_name'][0]));
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

}
