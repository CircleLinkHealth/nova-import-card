<?php namespace App\Http\Controllers\qliqSOFT;

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
        $getApiKey = ThirdPartyApiConfig::select('meta_value')->whereMetaKey('qliqsoft_api_key')->first();

        if (!empty($getApiKey)) {
            return redirect()->action('qliqSOFT\ConfigController@edit', [ 'api-config' ]);
        }

        return view('thirdPartyApisConfig.qliqsoft.create');
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
        $getApiKey = ThirdPartyApiConfig::select('meta_value')->whereMetaKey('qliqsoft_api_key')->first();
        if (!empty($getApiKey)) {
            $apiKey = $getApiKey['meta_value'];
        }

        $getApiUrl = ThirdPartyApiConfig::select('meta_value')->whereMetaKey('qliqsoft_api_url')->first();
        if (!empty($getApiUrl)) {
            $apiUrl = $getApiUrl['meta_value'];
        }

        if (empty($apiKey) && empty($apiUrl)) {
            return redirect()->action('qliqSOFT\ConfigController@create');
        }

        return view('thirdPartyApisConfig.qliqsoft.edit', [
            'apiKey' => $apiKey,
            'apiUrl' => $apiUrl,
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
        $getApiKey = ThirdPartyApiConfig::whereMetaKey('qliqsoft_api_key')->first();
        $getApiKey->meta_value = $request->input('qliqsoft_api_key');
        $getApiKey->save();

        $getApiUrl = ThirdPartyApiConfig::whereMetaKey('qliqsoft_api_url')->first();
        $getApiUrl->meta_value = $request->input('qliqsoft_api_url');
        $getApiUrl->save();

        return redirect()->back();
    }
}
