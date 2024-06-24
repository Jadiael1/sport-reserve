<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifique o endereço de e-mail')
            ->line('Clique no botão abaixo para verificar seu endereço de e-mail.')
            ->action('Verificar e-mail', $this->verificationUrl($notifiable))
            ->line('Se você não criou uma conta, nenhuma ação adicional será necessária.');
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $appUrl = env('SAP_URL', 'localhost');
        $apiUrl = "{$appUrl}/email/verify/{$notifiable->getKey()}?expires=".now()->addMinutes(config('auth.verification.expire', 60))->timestamp."&signature=".sha1($notifiable->getEmailForVerification());

        return $apiUrl;
    }

}
