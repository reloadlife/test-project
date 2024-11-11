<?php

namespace App\Listeners;

use App\Events\BasketClearIncoming;
use App\Notifications\BasketExpirationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyUserBasketClearIncoming implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(BasketClearIncoming $event): void
    {
        Notification::send(
            $event->basket->user,
            new BasketExpirationNotification($event->basket->items->count())
        );
    }
}
