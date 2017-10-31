<?php namespace App\Http\Middleware;

use Closure;
use App\ThirdPartyApiConfig;
use App\Services\Redox\RedoxAuthentication as RedoxAuthenticator;

class GetRedoxAccessToken
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $retrieveAccessToken = ThirdPartyApiConfig::whereMetaKey('redox_access_token')->first();
        $retrieveRefreshToken = ThirdPartyApiConfig::whereMetaKey('redox_refresh_token')->first();
        $retreveExpires = ThirdPartyApiConfig::whereMetaKey('redox_expires')->first();

        $authentication = new RedoxAuthenticator;

        /*
         * If there's no access token, or if it's expired => authenticate
         * Otherwise => authenticate with refresh token
         */
        if (empty($retrieveAccessToken['meta_value'])) {
            $authentication->authenticate();
        } elseif (strtotime($retreveExpires['meta_value']) < strtotime('now')) {
            $authentication->authenticate();
        } else {
            $authentication->authenticateWithRefreshToken($retrieveRefreshToken['meta_value']);
        }

        return $next($request);
    }
}
