<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Notifications\AppointmentReminder;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send reminders for appointments scheduled tomorrow';

    /**
     * The console command description.
     *
     * @var string
     */
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = now()->addDay();
        $appointments = Appointment::where('status', 'confirmed')
            ->whereDate('start_time', $tomorrow->toDateString())
            ->get();

        $this->info("Found {$appointments->count()} appointments for tomorrow.");

        foreach ($appointments as $appointment) {
            $appointment->user->notify(new AppointmentReminder($appointment));
            $this->info("Sent reminder for appointment #{$appointment->id}");
        }

        return Command::SUCCESS;
    }
}
