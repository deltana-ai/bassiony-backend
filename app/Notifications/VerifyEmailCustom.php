<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailCustom extends Notification implements ShouldQueue
{
    use Queueable;
    protected string $url;

    /**
     * Create a new notification instance.
     */
     public function __construct(string $url)
     {
         $this->url = $url;
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
        ->subject('✅ Verify Your Email Address')
        ->greeting('Hello ' . $notifiable->name . ' 👋')
        ->line('Please verify your email address by clicking the button below.')
        ->action('Verify Email', $this->url)
        ->line('If you did not create an account, no further action is required.')
        ->salutation('Regards, ' . config('app.name'));

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
