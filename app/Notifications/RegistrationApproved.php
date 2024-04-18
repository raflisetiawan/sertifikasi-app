<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationApproved extends Notification
{
    use Queueable;
    public $registration;

    /**
     * Create a new notification instance.
     */
    public function __construct($registration)
    {
        $this->registration = $registration;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('Pendaftaran anda telah di setujui')
        ->greeting('')
        ->line('Pendaftaran untuk kelas ' . $this->registration->course->name)
        ->action('Lihat Kelas', url(env('FRONT_URL') . 'my_profile/course/' .$this->registration->course->id))
        ->line('Terima kasih!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
