<?php

namespace App\Listeners;

use App\Events\PasswordRecoveryEvent;
use App\Mail\PasswordRecoveryMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PasswordRecoveryListener implements ShouldQueue
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
        Mail::to($event->user->email)->send(new PasswordRecoveryMail());
    }
}
