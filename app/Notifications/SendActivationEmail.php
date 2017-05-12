<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendActivationEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
        $this->onQueue('social');
    }

    public function via($notifiable)
    {
        return ['mail'];
    }


    public function toMail($notifiable)
    {

        $message = new MailMessage;
        $message->subject(trans('emails.activationSubject'))
            ->greeting(trans('emails.activationGreeting'))
            ->line(trans('emails.activationMessage'))
            ->action(trans('emails.activationButton'), route('authenticated.activate', ['token' => $this->token]))
            ->line(trans('emails.activationThanks'));

        return ($message);

    }

    /**
     *  повідомлення
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
