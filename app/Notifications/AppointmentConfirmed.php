<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentConfirmed extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
     // Email channel format
    public function toMail($notifiable)
    {
         return (new MailMessage)
             ->subject('Your Appointment is Confirmed')
             ->greeting('Hello ' . $notifiable->name . '!')
             ->line('Your appointment has been confirmed.')
             ->line('Date: ' . $this->appointment->start_time->format('M d, Y'))
             ->line('Time: ' . $this->appointment->start_time->format('h:i A') . ' - ' . $this->appointment->end_time->format('h:i A'))
             ->action('View Appointment', url('/appointments/' . $this->appointment->id))
             ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'message' => 'Your appointment has been confirmed.',
            'start_time' => $this->appointment->start_time,
        ];
    }
}
