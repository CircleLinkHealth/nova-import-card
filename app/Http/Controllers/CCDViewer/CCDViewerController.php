<?php namespace App\Http\Controllers\CCDViewer;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\XmlCCD;
use Illuminate\Http\Request;

class CCDViewerController extends Controller {

	public function viewByUserId($id)
    {
        $ccd = XmlCCD::whereUserId($id)->first()->ccd;

        $template = view('CCDViewer.bb-ccd-viewer', compact('ccd'))->render();

        return view('CCDViewer.viewer', compact('template'));
    }

}
