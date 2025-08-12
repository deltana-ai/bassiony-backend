<?php

use Carbon\Carbon;

function dateCounter($date)
{
    // Get the current date and time
    $now = Carbon::now();
    // Parse the input date and time
    $date = Carbon::parse($date);

    // Calculate the difference in total months and days
    $total_months = $date->diffInMonths($now);
    $months_passed = $date->copy()->addMonths($total_months)->diffInMonths($now);
    $days_passed = $date->copy()->addMonths($total_months)->diffInDays($now);

    // Adjust the days_passed to include the last day of the month
    if ($days_passed > 0) {
        $months_passed--;
        $days_passed = $now->copy()->addMonths($months_passed)->diffInDays($date);
    }

    // Construct a message displaying the time passed in months and days
    $message = '';
    if ($months_passed > 0) {
        $message .= $months_passed . ' Month';
        if ($months_passed > 1) {
            $message .= 's';
        }
        $message .= ', ';
    }
    $message .= $days_passed . ' Day';
    if ($days_passed > 1) {
        $message .= 's';
    }

    // Add "ago" if the date is in the past
    if ($date < $now) {
        $message .= ' ago';
    }

    return $message;
}





