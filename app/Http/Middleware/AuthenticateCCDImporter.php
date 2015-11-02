<?php namespace App\Http\Middleware;

use App\ApiKey;
use Carbon\Carbon;
use Closure;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Request;

class AuthenticateCCDImporter {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$credentials = json_decode( Crypt::decrypt($request->input('key')), true );

		if(!empty($apiKey = $credentials['apiKey']))
		{
			if ( ! ApiKey::checkKeyExists($apiKey) )
			{
				throw new \Exception('Unauthorized request.', 401);
			}
		}

		if(!empty($time = $credentials['time']))
		{
			if ( Carbon::createFromTimestampUTC($time)->diffInMinutes() > 30 )
			{
				throw new \Exception('Access token expired. Please initiate a new CCD Importer session.', 400);
			}
		}

		if(!empty($blogId = $credentials['blogId']))
		{
			if($blogId != $request->id)
			{
				throw new \Exception('Blog id error.', 400);
			}

			$request->session()->put('blogId', $blogId);
		}

		return $next($request);
	}

}
