<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('borrow:notify-schedule')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('inventory:notify-suppliers')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('equipment:notify-lifespan-expiry')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('reports:generate-quarterly')
    ->quarterly()
    ->at('00:00')
    ->withoutOverlapping();
