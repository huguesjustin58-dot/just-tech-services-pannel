<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('jts:vpn-sync-accounts')->everyMinute()->withoutOverlapping();
