<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

trait ManagesPatientCookies
{
    private function checkPracticeNameCookie(Request $request)
    {
        if (auth()->check()) {
            $user = auth()->user();

            //if user is not participant forget cookie if exists. Mostly need this in QA/testing.
            if ( ! $user->isParticipant()) {
                $this->forgetCookie();

                return;
            }

            //if practice name in cookie is wrong, fix
            if ($user->primaryPractice->display_name != $this->getCookie()) {
                $this->forgetCookie();
            }

            $this->setCookie($user->primaryPractice);

            return;
        }

        //if user is not authenticated, look for practice id.
        if ($request->has('practice_id')) {
            $practice = Practice::find($request->input('practice_id'));

            if ( ! $practice) {
                \Log::info("Invalid Practice ID for cookie: {$this->practiceNameCookieKey()}.", [
                    'practice_id' => $request->input('practice_id'),
                ]);

                return;
            }

            $this->setCookie($practice);
        }
    }

    private function cookieExists()
    {
        return Cookie::has($this->practiceNameCookieKey());
    }

    private function forgetCookie()
    {
        if ($this->cookieExists()) {
            unset($_COOKIE['practice_name_as_logo']);
            Cookie::queue(Cookie::forget('practice_name_as_logo'));
        }
    }

    private function getCookie()
    {
        return Cookie::get($this->practiceNameCookieKey());
    }

    private function practiceNameCookieKey()
    {
        return 'practice_name_as_logo';
    }

    private function setCookie(Practice $practice)
    {
        //We need the cookie to be available by the time the view renders. (no-auth.blade)
        //Laravel cookie facade adds the cookie after the view is rendered. This makes it so that you can see the practice name after the first load of the page
        //This hack makes it so that we can have the practice name at first load.
        //Also it gets immediately overwritten by the Laravel facade.
        if ( ! isset($_COOKIE['practice_name_as_logo'])) {
            $_COOKIE['practice_name_as_logo'] = $practice->display_name;
        }

        Cookie::queue(Cookie::make($this->practiceNameCookieKey(), $practice->display_name));
    }
}
