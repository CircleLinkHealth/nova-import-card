<?php namespace App\Http\Controllers\CcdApi\Aprima;

use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\ItemLogger\CcdItemLogger;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CLH\CCD\ValidatesQAImportOutput;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CcdApiController extends Controller
{

    use ValidatesQAImportOutput;

    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
    }

    public function reports(Request $request)
    {

        $sample = array();
        $sample[0] = [
            'patientId' => '103',
            'providerId' => '100',
            'file' => base64_encode(file_get_contents(base_path('storage/pdfs/careplans/sample-careplan.pdf'))),
            'fileType' => 'careplan',
        ];

        return response()->json($sample);
    }

    public function uploadCcd(Request $request)
    {
        if (!\Session::has('apiUser')) {
            response()->json(['error' => 'Authentication failed.'], 403);
        }

        $user = \Session::get('apiUser');

        if (!$user->can('post-ccd-to-api')) {
            response()->json(['error' => 'You are not authorized to submit CCDs to this API.'], 403);
        }

        if (!$request->has('file')) {
            response()->json(['error' => 'No file found on the request.'], 422);
        }

        $programId = $user->blogId();

        try {
            $xml = base64_decode($request->input('file'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to base64_decode CCD.'], 400);
        }

        $ccdObj = Ccda::create([
            'user_id' => $user->ID,
            'vendor_id' => 1,
            'xml' => $xml,
        ]);

        //We are saving the JSON CCD after we save the XML, just in case Parsing fails
        //If Parsing fails we let ourselves know, but not Aprima.
        try {
            $json = $this->repo->toJson($xml);
            $ccdObj->json = $json;
            $ccdObj->save();
        } catch (\Exception $e) {
            if (app()->environment('production')) {
                $this->notifyAdmins($user, $ccdObj, 'bad', __METHOD__ . ' ' . __LINE__, $e->getMessage());
            }
            return response()->json(['message' => 'CCD uploaded successfully.'], 201);
        }


        //If Logging fails we let ourselves know, but not Aprima.
        try {
            $logger = new CcdItemLogger($ccdObj);
            $logger->logAll();
        } catch (\Exception $e) {
            if (app()->environment('production')) {
                $this->notifyAdmins($user, $ccdObj, 'bad', __METHOD__ . ' ' . __LINE__, $e->getMessage());
            }
            return response()->json(['message' => 'CCD uploaded successfully.'], 201);
        }

        //If Logging fails we let ourselves know, but not Aprima.
        //Yes. Repetitions. I KNOW!
        try {
            $importer = new QAImportManager($programId, $ccdObj);
            $output = $importer->generateCarePlanFromCCD();
        } catch (\Exception $e) {
            if (app()->environment('production')) {
                $this->notifyAdmins($user, $ccdObj, 'bad', __METHOD__ . ' ' . __LINE__, $e->getMessage());
            }
            return response()->json(['message' => 'CCD uploaded successfully.'], 201);
        }

        if (app()->environment('production')) {
            $this->notifyAdmins($user, $ccdObj, 'well');
        }

        return response()->json(['message' => 'CCD uploaded successfully.'], 201);
    }

    /**
     * This is to help notify us of the status of CCDs we receive.
     *
     *
     * @param User $user
     * @param Ccda $ccda
     * @param $status
     * @param null $line
     * @param null $errorMessage
     */
    public function notifyAdmins(User $user, Ccda $ccda, $status, $line = null, $errorMessage = null)
    {
        $recipients = [
            'Plawlor@circlelinkhealth.com',
            'rohanm@circlelinkhealth.com',
            'mantoniou@circlelinkhealth.com'
        ];

        $view = 'emails.aprimaSentCCDs';
        $subject = "Aprima sent a CCD. It went {$status}";

        $data = [
            'ccdId' => $ccda->id,
            'errorMessage' => $errorMessage,
            'userId' => $user->ID,
            'line' => $line,
        ];

        Mail::send($view, $data, function ($message) use ($recipients, $subject) {
            $message->from('aprima-api@careplanmanager.com', 'CircleLink Health');
            $message->to($recipients)->subject($subject);
        });
    }
}
