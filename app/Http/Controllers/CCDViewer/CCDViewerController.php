<?php namespace App\Http\Controllers\CCDViewer;

use App\Models\CCD\Ccda;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JavaScript;

class CCDViewerController extends Controller
{

    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
        ini_set( 'memory_limit', '-1' );
    }

    public function create()
    {
        return view( 'CCDViewer.old-viewer' );
    }

    public function showByUserId($userId)
    {
        $ccd = Ccda::wherePatientId( $userId )->first()->ccd;

        $template = view( 'CCDViewer.bb-ccd-viewer', compact( 'ccd' ) )->render();

        return view( 'CCDViewer.viewer', compact( 'template' ) );
    }

    public function showUploadedCcd(Request $request)
    {
        if ( $request->hasFile( 'uploadedCcd' ) ) {
            $ccd = file_get_contents( $request->file( 'uploadedCcd' ) );

            $template = view( 'CCDViewer.bb-ccd-viewer', compact( 'ccd' ) )->render();

            return view( 'CCDViewer.viewer', compact( 'template' ) );
        }
    }

    public function oldViewer(Request $request)
    {
        if ( $request->hasFile( 'uploadedCcd' ) ) {
            $xml = file_get_contents( $request->file( 'uploadedCcd' ) );

            $ccd = json_decode( $this->repo->toJson( $xml ) );

            return view( 'CCDViewer.old-viewer', compact( 'ccd' ) );
        }
    }

    public function viewSource(Request $request)
    {
        if ( $xml = urldecode( $request->input( 'xml' ) ) ) {
            return view( 'CCDViewer.old-viewer', compact( 'xml' ) );
        }
    }

}
