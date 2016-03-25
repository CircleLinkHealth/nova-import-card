<?php namespace App\Http\Controllers\CcdApi\Aprima;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class CCMTimeController extends Controller {

	public function getCcmTime()
	{
		$demo = [
			'patientId' => 103,
			'providerId' => 100,
			'careEvents' => [
				'servicePerson' => 'Bob',
				'startingDateTime' => '2015-05-26 18:32:00',
				'length' => '10',
				'commentstring' => 'Call Center Contact'
			]
		];

		return json_encode($demo, JSON_FORCE_OBJECT);
	}
}
