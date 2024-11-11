<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class NewProduct extends Notification
{
    // use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Product $product)
    {
        Log::debug("Initialized a new product notification", [
            "product" => $product,
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        return (new MailMessage)
            ->subject('New Product Created')
            ->line('A new product has been created:')
            ->line('Name: ' . $this->product->name)
            ->line('Price: ' . $this->product->price)
            ->line('Stock: ' . $this->product->stock)
            ->action('View Product', url('/products/' . $this->product->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
