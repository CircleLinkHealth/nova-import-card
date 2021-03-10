<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('/short/{shortURLKey}', 'AshAllenDesign\ShortURL\Controllers\ShortURLController')->name('short-url.invoke');
