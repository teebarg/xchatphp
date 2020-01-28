<?php

namespace App\Listeners;

use App\Events\NewCustomerEvent;
use App\Mail\WelcomeNewUserMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NewCustomerListener implements ShouldQueue
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
     * @param  NewCustomerEvent  $event
     * @return void
     */
    public function handle(NewCustomerEvent $event)
    {
//        dd($event->user);
        Mail::to($event->user->email)->send(new WelcomeNewUserMail($event->user));
    }
}
