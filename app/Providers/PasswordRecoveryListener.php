<?php

namespace App\Providers;

use App\Providers\PasswordRecoveryEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PasswordRecoveryListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PasswordRecoveryEvent  $event
     * @return void
     */
    public function handle(PasswordRecoveryEvent $event)
    {
        //
    }
}
