<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace AshAllenDesign\ShortURL\Controllers;

use AshAllenDesign\ShortURL\Classes\Resolver;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShortURLController
{
    /**
     * Redirect the user to the intended destination
     * URL. If the default route has been disabled
     * in the config but the controller has been
     * reached using that route, return HTTP
     * 404.
     */
    public function __invoke(Request $request, Resolver $resolver, string $shortURLKey): RedirectResponse
    {
        if ('short-url.invoke' === $request->route()->getName()
            && config('short-url.disable_default_route')) {
            abort(404);
        }

        $shortURL = ShortURL::where('url_key', $shortURLKey)->firstOrFail();

        $resolver->handleVisit(request(), $shortURL);

        return redirect($shortURL->destination_url, $shortURL->redirect_status_code);
    }
}
