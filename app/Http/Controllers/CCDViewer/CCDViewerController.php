<?php namespace App\Http\Controllers\CCDViewer;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\XmlCCD;
use Illuminate\Http\Request;
use JavaScript;

class CCDViewerController extends Controller {

	public function showByUserId($userId)
    {
        $ccd = XmlCCD::whereUserId($userId)->first()->ccd;

        $template = view('CCDViewer.bb-ccd-viewer', compact('ccd'))->render();

        return view('CCDViewer.viewer', compact('template'));
    }

    public function showUploadedCcd(Request $request)
    {
        if ($request->hasFile('uploadedCcd'))
        {
            $ccd = file_get_contents($request->file('uploadedCcd'));

            $template = view('CCDViewer.bb-ccd-viewer', compact('ccd'))->render();

            return view('CCDViewer.viewer', compact('template'));
        }
    }

    public function oldViewer(Request $request)
    {
//        $xml = base64_decode($request->input('xml'));
        $xml = urldecode($request->input('xml'));

        return view('CCDViewer.old-viewer', compact('xml'));
    }

}
