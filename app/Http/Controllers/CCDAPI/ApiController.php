<?php namespace App\Http\Controllers\CCDAPI;

use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ApiController extends Controller
{

    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
    }

    //returns JWT session token
    public function login(Request $request)
    {
        if (! $request->has('email') || ! $request->has('user_pass')) {
            response()->json(['error' => 'Invalid Request'], 400);
        }

        $credentials = \Input::only('email', 'user_pass');
        \JWTAuth::setIdentifier('ID');
        if (!$token = \JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 400);
        }
        return response()->json(compact('token'), 200);
    }

    public function create(Request $request)
    {
        $user = \JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['error' => 'Invalid Token'], 400);
        }

        if (!$request->has('ccd')) {
            response()->json(['error' => 'No file found on the request.'], 422);
        }

        $blog = $user->blogId();
        if (!$user->hasRole('ccd-vendor')) {
            response()->json(['error' => 'Unauthorized Request'], 403);
        }

        $xml = base64_decode($request['ccd']);
        $json = $this->repo->toJson($xml);
        $ccdObj = Ccda::create([
            'user_id' => $user->ID,
            'vendor_id' => 1,
            'xml' => $xml
        ]);

        $ccdObj->json = $json;
        $ccdObj->save();

        $importer = new QAImportManager($blog, $ccdObj);
        $output = $importer->generateCarePlanFromCCD();

        $jsonCcd = json_decode($output->output, true);

        return response()->json($jsonCcd);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}
