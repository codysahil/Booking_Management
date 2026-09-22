<?php

namespace App\Notifications;

use App\Models\Request as ResidentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Emails the resident when staff respond to their request (only if they have an email). */
class RequestStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public ResidentRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        return empty($notifiable->email) ? [] : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(sprintf('Your %s request is %s', strtolower($this->request->type_label), $this->request->status))
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line(sprintf('Your request "%s" has been marked as %s.', $this->request->subject, $this->request->status));

        if ($this->request->admin_response) {
            $mail->line('Message from the office: ' . $this->request->admin_response);
        }

        return $mail
            ->action('View request', route('customer.requests.show', $this->request))
            ->salutation('— ' . setting('hostel_name'));
    }
}
