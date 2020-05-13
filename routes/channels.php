<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
