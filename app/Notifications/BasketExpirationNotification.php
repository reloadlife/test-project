<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class BasketExpirationNotification extends Notification
{
    // use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly int $itemCount) {
        Log::debug("Initialized a clear basket notification");
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::debug(
            "user was notified about their basket being cleared in an hour",
        );
        return (new MailMessage)
            ->subject('Your Shopping Basket Will Expire Soon')
            ->line('Your shopping basket with ' . $this->itemCount . ' items will expire in 1 hour.')
            ->line('Please complete your purchase to keep these items.')
            ->action('View Basket', url('/basket'))
            ->line('Thank you for shopping with us!');
    }
}
