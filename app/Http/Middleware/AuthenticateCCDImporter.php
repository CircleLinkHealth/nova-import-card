<?php namespace App\Http\Middleware;

use App\ApiKey;
use Carbon\Carbon;
use Closure;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Request;

class AuthenticateCCDImporter
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! auth()->user()->can( 'ccd-import' ) )
        {
            abort(403, 'You do not have permission to import CCDs.');
        }

        return $next( $request );
    }

}
