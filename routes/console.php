<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('attendance:students-evaluate')->everyFiveMinutes()->withoutOverlapping();
