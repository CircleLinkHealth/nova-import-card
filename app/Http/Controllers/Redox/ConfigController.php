<?php namespace App\Http\Controllers\Redox;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\ThirdPartyApiConfig;
use Illuminate\Http\Request;

class ConfigController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $getApiKey = ThirdPartyApiConfig::select('meta_value')->whereMetaKey('redox_api_key')->first();

        if (!empty($getApiKey)) {
            return redirect()->action('Redox\ConfigController@edit', [ 'api-config' ]);
        }

        return view('thirdPartyApisConfig.redox.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        foreach ($request->input() as $key => $value) {
            if ($key == '_token') {
                continue; //don't store the form csrf_token in the db
            }

            $newConfig = new ThirdPartyApiConfig();
            $newConfig->meta_key = $key;
            $newConfig->meta_value = $value;
            $newConfig->save();
        }

        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit()
    {
        $getApiKey = ThirdPartyApiConfig::select('meta_value')->whereMetaKey('redox_api_key')->first();
        if (!empty($getApiKey)) {
            $apiKey = $getApiKey['meta_value'];
        }

        $getApiSecret = ThirdPartyApiConfig::select('meta_value')->whereMetaKey('redox_api_secret')->first();
        if (!empty($getApiSecret)) {
            $apiSecret = $getApiSecret['meta_value'];
        }

        $getAppVerifToken = ThirdPartyApiConfig::select('meta_value')->whereMetaKey('redox_app_verification_token')->first();
        if (!empty($getAppVerifToken)) {
            $appVerifToken = $getAppVerifToken['meta_value'];
        }


        if (empty($apiKey) && empty($apiSecret) && empty($appVerifToken)) {
            return redirect()->action('Redox\ConfigController@create');
        }

        $accessTokens = ThirdPartyApiConfig::select('meta_key', 'meta_value')
            ->whereMetaKey('redox_access_token')
            ->orWhere('meta_key', 'redox_expires')
            ->orWhere('meta_key', 'redox_refresh_token')
            ->get()
            ->toArray();

        return view('thirdPartyApisConfig.redox.edit', [
            'apiKey' => $apiKey,
            'apiSecret' => $apiSecret,
            'appVerifToken' => $appVerifToken,
            'accessTokens' => $accessTokens,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request)
    {
        $getApiKey = ThirdPartyApiConfig::whereMetaKey('redox_api_key')->first();
        $getApiKey->meta_value = $request->input('redox_api_key');
        $getApiKey->save();

        $getApiSecret = ThirdPartyApiConfig::whereMetaKey('redox_api_secret')->first();
        $getApiSecret->meta_value = $request->input('redox_api_secret');
        $getApiSecret->save();

        $getAppVerifToken = ThirdPartyApiConfig::whereMetaKey('redox_app_verification_token')->first();
        $getAppVerifToken->meta_value = $request->input('redox_app_verification_token');
        $getAppVerifToken->save();

        return redirect()->back();
    }
}
