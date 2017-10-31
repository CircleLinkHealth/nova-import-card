<?php namespace App;

use Chrisbjr\ApiGuard\Repositories\ApiLogRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ApiLog extends ApiLogRepository
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_api_logs';

    public function logThisRequest(Request $request, $apiKey)
    {
        $logger = new ApiLog();
        $apiKey = ApiKey::where('key', '=', $apiKey)->limit(1)->get();

        $logger->api_key_id = $apiKey[0]['id'];
        $logger->route      = Route::currentRouteAction();
        $logger->method     = $request->getMethod();
        $logger->params     = http_build_query($request->input());
        $logger->ip_address = $request->getClientIp();
        $logger->save();
    }
}
