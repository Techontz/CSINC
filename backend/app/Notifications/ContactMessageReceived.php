<?php

namespace App\Notifications;

use App\Content\ConsultationForm;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $contactMessage) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->contactMessage;

        $mail = (new MailMessage)
            ->subject('New consultation inquiry — '.$message->full_name)
            ->replyTo($message->email, $message->full_name)
            ->greeting('New inquiry received')
            ->line('**Name:** '.e($message->full_name))
            ->line('**Email:** '.e($message->email))
            ->line('**Phone:** '.e((string) $message->phone));

        foreach (ConsultationForm::detailLabels() as $key => $label) {
            $value = $message->details[$key] ?? null;

            if (filled($value)) {
                $mail->line('**'.$label.':** '.e(is_array($value) ? implode(', ', array_filter($value)) : (string) $value));
            }
        }

        return $mail
            ->line('**Message:**')
            ->line(e($message->message))
            ->action('Open in admin', url('/admin/contact-messages/'.$message->getKey()));
    }
}
