<?php

use Illuminate\Support\Facades\Schedule;

// Konvensional: Scheduled task cron job setiap menit
Schedule::command('reminders:poll-send')->everyMinute();
