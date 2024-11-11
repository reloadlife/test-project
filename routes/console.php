<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('telescope:prune --hours=48')->daily();

Schedule::command('app:daily-clear-user-basket --notify')
    ->dailyAt('23:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/basket-notifications.log'));

Schedule::command('app:daily-clear-user-basket')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/basket-cleanup.log'));
