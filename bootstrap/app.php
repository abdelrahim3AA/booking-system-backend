<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule; // Add this line
use App\Notifications\AppointmentReminder;
use App\Models\Appointment;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // Send reminders 24 hours before appointments
        $schedule->call(function () {
            $tomorrow = now()->addDay();
            $appointments = Appointment::where('status', 'confirmed')
                ->whereDate('start_time', $tomorrow->toDateString())
                ->get();

            foreach ($appointments as $appointment) {
                $appointment->user->notify(new AppointmentReminder($appointment));
            }
        })->dailyAt('09:00');
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('appointments:send-reminders')->dailyAt('09:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
