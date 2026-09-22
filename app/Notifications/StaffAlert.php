<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Base class for alerts shown in the admin notification centre (database channel).
 */
abstract class StaffAlert extends Notification
{
    use Queueable;

    /** One of: booking, request, payment, info */
    abstract protected function kind(): string;

    abstract protected function title(): string;

    abstract protected function message(): string;

    abstract protected function url(): string;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind(),
            'title' => $this->title(),
            'message' => $this->message(),
            'url' => $this->url(),
        ];
    }
}
