<?php namespace App\Http\Middleware;

use Closure;

class Secure {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (! $request->secure()
			&& ! in_array(app()->environment(), ['local', 'testing'])) {
			return redirect()->secure($request->getRequestUri());
		}

		return $next($request);
	}

}
