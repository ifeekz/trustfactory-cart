<?php

use App\Console\Commands\SendDailySalesReport;

use Illuminate\Support\Facades\Schedule;

Schedule::command(SendDailySalesReport::class)->dailyAt('18:00');
