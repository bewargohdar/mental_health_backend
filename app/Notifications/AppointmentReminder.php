<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification implements ShouldQueue
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
        $isPatient = $notifiable->id === $this->appointment->patient_id;
        $with = $isPatient ? $this->appointment->doctor->name : $this->appointment->patient->name;

        return (new MailMessage)
            ->subject('Appointment Reminder - Tomorrow')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder for your upcoming appointment.')
            ->line('**With:** ' . $with)
            ->line('**Date & Time:** ' . $this->appointment->scheduled_at->format('F j, Y g:i A'))
            ->line('**Duration:** ' . $this->appointment->duration . ' minutes')
            ->action('View Appointment', url('/appointments/' . $this->appointment->id))
            ->line('Please be ready a few minutes before your scheduled time.');
    }

    public function toArray(object $notifiable): array
    {
        $isPatient = $notifiable->id === $this->appointment->patient_id;
        $with = $isPatient ? $this->appointment->doctor->name : $this->appointment->patient->name;

        return [
            'type' => 'appointment_reminder',
            'appointment_id' => $this->appointment->id,
            'with' => $with,
            'scheduled_at' => $this->appointment->scheduled_at->toIso8601String(),
            'message' => 'Reminder: You have an appointment tomorrow at ' . $this->appointment->scheduled_at->format('g:i A'),
        ];
    }
}
