<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Appointment Confirmed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your appointment has been confirmed.')
            ->line('**Doctor:** ' . $this->appointment->doctor->name)
            ->line('**Date & Time:** ' . $this->appointment->scheduled_at->format('F j, Y g:i A'))
            ->line('**Duration:** ' . $this->appointment->duration . ' minutes')
            ->action('View Appointment', url('/appointments/' . $this->appointment->id))
            ->line('Please be ready a few minutes before your scheduled time.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'appointment_confirmed',
            'appointment_id' => $this->appointment->id,
            'doctor_name' => $this->appointment->doctor->name,
            'scheduled_at' => $this->appointment->scheduled_at->toIso8601String(),
            'message' => 'Your appointment with Dr. ' . $this->appointment->doctor->name . ' has been confirmed.',
        ];
    }
}
