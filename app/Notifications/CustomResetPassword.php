<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class CustomResetPassword extends ResetPasswordNotification
{
    use Queueable;

    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        $expirationMinutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
        $expiration = Carbon::now()->addMinutes($expirationMinutes)->setTimezone('America/Recife');
        return (new MailMessage)
            ->subject('Notificação de redefinição de senha')
            ->line('Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha da sua conta.')
            ->action('Redefinir senha', $url)
            ->line('Este link de redefinição de senha irá expirar em ' . $expiration->toDateTimeString() . '.')
            ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional será necessária.');
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        $appUrl = env('SAP_URL', 'https://sport-reserve.juvhost.com');
        $email = urlencode($notifiable->getEmailForPasswordReset());
        return "{$appUrl}/auth/reset-password?token={$this->token}&email={$email}";
    }
}
