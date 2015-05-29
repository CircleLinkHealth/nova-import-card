<?php namespace App\Http\Controllers;

use App\ApiKey;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ApiKeyController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$apiKeys = ApiKey::all()->sortBy('client_name');

		return view('apiKeys.index', [ 'apiKeys' => $apiKeys ]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$clientName = $request->input('client_name');

		$apiKey = new ApiKey();
		$apiKey->key = $apiKey->generateKey();
		$apiKey->client_name = $clientName;

		if ($apiKey->save() === false) {
			$messageKey = 'error';
			$messageValue = 'Failed to save API key to the database.';
		} else {
			$messageKey = 'success';
			$messageValue = "Generated {$apiKey->key} API key for {$apiKey->client_name}";
		}

//		Come back here letter.
//		Figure out how to redirect back with input
//		return redirect()->back()->with([ $messageKey => $messageValue ]);

		$apiKeys = ApiKey::all()->sortBy('client_name');

		return view('apiKeys.index', [ 'apiKeys' => $apiKeys,
										$messageKey => $messageValue
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		ApiKey::destroy($id);

//		Come back here letter.
//		Figure out how to redirect back with input
//		return redirect()->back()->with([ 'success' => "Successfully deleted key!" ]);

		$apiKeys = ApiKey::all()->sortBy('client_name');

		return view('apiKeys.index', [ 'apiKeys' => $apiKeys,
			'success' => 'Successfully deleted key!'
		]);
	}

}
