<?php

namespace App\Console\Commands;

use App\Events\BasketClearIncoming;
use App\Models\Basket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyClearUserBasket extends Command
{
    protected $signature = 'app:daily-clear-user-basket {--notify : Send notifications to users before clearing}';

    protected $description = 'Clears user\'s basket after 24 hours of inactivity.';

    public function handle(): void
    {
        Log::info('Starting to process inactive baskets...');
        $this->info('Starting to process inactive baskets...');

        if ($this->option('notify')) {
            $this->info('Sending notifications to users...');
            Log::info('Sending notifications to users...');
            $this->notifyUsersWithExpiringBaskets();
        }


        $this->clearExpiredBaskets();
    }

    private function notifyUsersWithExpiringBaskets(): void
    {
        // log everything

        Log::debug("Baskets: ", [
            "baskets" => Basket::query()->get()
        ]);

        $expiringBaskets = Basket::query()
            ->where('updated_at', '<=', now()->subHours(23))
            ->whereHas('items')
            ->with(['user', 'items'])
            ->get();

        Log::debug("Expiring baskets found: {$expiringBaskets->count()}");

        foreach ($expiringBaskets as $basket) {
            BasketClearIncoming::dispatch($basket);
            Log::info("Sent expiration notification for basket #{$basket->id}");
            $this->info("Sent expiration notification for basket #{$basket->id}");
        }
    }

    private function clearExpiredBaskets(): void
    {
        Log::debug("Baskets: ", [
            "baskets" => Basket::query()->get()
        ]);

        Basket::where('updated_at', '<=', now()->subHours(24))
            ->whereHas('items')
            ->chunk(100, function ($baskets) {
                foreach ($baskets as $basket) {
                    Log::info('Clearing inactive basket', [
                        'basket_id' => $basket->id,
                        'user_id' => $basket->user_id,
                        'items_count' => $basket->items->count(),
                        'total_price' => $basket->total_price
                    ]);

                    $basket->items()->delete();
                    $basket->delete();

                    Log::info("Cleared basket #{$basket->id}");
                    $this->info("Cleared basket #{$basket->id}");
                }
            });
        $this->info("Basket processing completed successfully.");
        Log::info('Basket cleanup completed successfully');
    }
}
