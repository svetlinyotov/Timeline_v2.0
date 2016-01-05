<?php

namespace App\Handlers\Events;

use App\Events;
use App\User;
use Carbon\Carbon;

class LoginEventHandler
{
    /**
     * Create the event handler.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param User $user
     * @internal param Events $event
     */
    public function handle(User $user)
    {
        $user->last_login = Carbon::now();
        $user->save();
    }
}
