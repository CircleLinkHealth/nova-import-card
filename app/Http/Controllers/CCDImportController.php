<?php namespace App\Http\Controllers;

use App\CLH\CCD\Importer\ImportManager;
use App\CLH\CCD\QAImportOutput;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class CCDImportController extends Controller
{
    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
    }

    public function import(Request $request)
    {
        $import = $request->input('qaImportIds');

        foreach ( $import as $id ) {
            $output = QAImportOutput::find( $id );

            if ( empty($output) ) continue;

            $sections = json_decode( $output->output, true );

            $user = $this->repo->createRandomUser(
                $sections[ 'userConfig' ][ 'program_id' ],
                '',
                $sections[ 'userMeta' ][ 'first_name' ] . ' ' . $sections[ 'userMeta' ][ 'last_name' ]
            );

            $importer = new ImportManager($sections, $user);
            $importer->import();

            $imported[] = [
                'qaId' => $id,
                'userId' => $user->ID
            ];

            $output->delete();
        }

        return response()->json( compact('imported'), 200 );
    }

}
