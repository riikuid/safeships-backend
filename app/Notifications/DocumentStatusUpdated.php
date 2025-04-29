<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class DocumentStatusUpdated extends Notification
{
    use Queueable;

    protected $document;
    protected $action;

    /**
     * Create a new notification instance.
     */
    public function __construct($document, $action)
    {
        $this->document = $document;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toFcm($notifiable): FcmMessage
    {
        $title = '';
        $body = '';
        switch ($this->action) {
            case 'submitted':
                $title = 'Dokumen Baru Diajukan';
                $body = "Dokumen '{$this->document->title}' telah diajukan.";
                break;
            case 'approved':
                $title = 'Dokumen Disetujui';
                $body = "Dokumen '{$this->document->title}' telah disetujui.";
                break;
            case 'rejected':
                $title = 'Dokumen Ditolak';
                $body = "Dokumen '{$this->document->title}' ditolak.";
                break;
        }

        return (FcmMessage::create(notification: FcmNotification::create(
            title: $title,
            body: $body
        )))
            ->data([
                'document_id' => (string) $this->document->id,
                'action' => $this->action,
            ])
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#0A0A0A',
                        'sound' => 'default',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
            ]);
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'action' => $this->action,
            'message' => match ($this->action) {
                'submitted' => "Dokumen '{$this->document->title}' telah diajukan.",
                'approved' => "Dokumen '{$this->document->title}' telah disetujui.",
                'rejected' => "Dokumen '{$this->document->title}' ditolak.",
                default => '',
            },
        ];
    }
}
