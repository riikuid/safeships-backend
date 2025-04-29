<?php

namespace App\Helpers;

use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class FcmHelper
{
    public static function send($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken) {
            return;
        }

        \Illuminate\Support\Facades\Notification::route(FcmChannel::class, $fcmToken)
            ->notify(new class($title, $body, $data) extends \Illuminate\Notifications\Notification {
                protected $title;
                protected $body;
                protected $data;

                public function __construct($title, $body, $data)
                {
                    $this->title = $title;
                    $this->body = $body;
                    $this->data = $data;
                }

                public function via($notifiable)
                {
                    return [FcmChannel::class];
                }

                public function toFcm($notifiable): FcmMessage
                {
                    return (new FcmMessage(notification: new FcmNotification(
                        title: $this->title,
                        body: $this->body
                    )))
                        ->data($this->data)
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
            });
    }
}
