<?php

use Illuminate\Support\Facades\Schedule;

// Automation  run every day at 06:00 (app timezone).
// The `scheduler` service in docker-compose.yml keeps this running.
Schedule::command('alerts:check')->dailyAt('06:00')->withoutOverlapping();
