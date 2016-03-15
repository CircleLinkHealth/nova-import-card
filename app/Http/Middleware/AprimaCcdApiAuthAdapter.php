<?php namespace App\Http\Middleware;

use Closure;

class AprimaCcdApiAuthAdapter {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ( !$request->has( 'access_token' ) ) {
			return response()->json( ['error' => 'Access token not found on the request.'], 400 );
		}

		/**
		 * This will add an Authorization header to Aprima's request. We do this because we already gave
		 * them documentation for the separate API we had, and we don't wanna have them change their stuff.
		 */
		$request->headers->set( 'Authorization', "Bearer {$request->input('access_token')}" );


		return $next($request);
	}

}
