<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPassword extends Notification
{
    use Queueable;

    public $password;
    public $dashboard_type;

    public function __construct($password ,$dashboard_type)
    {
        $this->password = $password;
        $this->dashboard_type = $dashboard_type;
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
            ->subject('بيانات حسابك في النظام')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم إنشاء حسابك في النظام من قبل الإدارة.')
            ->line('البريد الإلكتروني: ' . $notifiable->email)
            ->line('كلمة المرور: ' . $this->password)
            ->line('يُرجى تغيير كلمة المرور بعد تسجيل الدخول لأول مرة.')
            ->action('تسجيل الدخول', url($this->dashboard_type.'/login'))
            ->line('شكرًا لاستخدامك منصتنا 💚');
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
