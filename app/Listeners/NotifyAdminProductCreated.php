<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Notifications\NewProduct;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyAdminProductCreated implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ProductCreated $event): void
    {
        Notification::route('mail', config('mail.admin.address'))
            ->notify(new NewProduct($event->product));
    }
}
