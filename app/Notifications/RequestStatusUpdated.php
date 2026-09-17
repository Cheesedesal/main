<?php

namespace App\Notifications;

use App\Models\DocumentRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusUpdated extends Notification
{
    public function __construct(public DocumentRequest $documentRequest, public string $message) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return ['message' => $this->message, 'request_id' => $this->documentRequest->id, 'tracking_code' => $this->documentRequest->tracking_code];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Document request update')->line($this->message)->action('View request', url('/requests/'.$this->documentRequest->id));
    }
}