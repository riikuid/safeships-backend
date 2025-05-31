<?php

namespace App\Helpers;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class FcmHelper
{
    /**
     * Kirim notifikasi FCM
     *
     * @param string $fcmToken
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public static function send(?string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (!$fcmToken) {
            Log::warning('FCM token is empty or null');
            return false;
        }

        try {
            // Membuat notifiable anonymous class dengan token FCM yang sudah diberikan
            $notifiable = new class($fcmToken) {
                protected string $fcmToken;

                public function __construct(string $fcmToken)
                {
                    $this->fcmToken = $fcmToken;
                }

                public function routeNotificationFor(string $channel)
                {
                    if ($channel === 'fcm') {
                        return $this->fcmToken;
                    }
                    return null;
                }
            };

            // Membuat notifikasi anonymous class yang mengirim payload ke FCM
            Notification::send($notifiable, new class($title, $body, $data) extends \Illuminate\Notifications\Notification {
                protected string $title;
                protected string $body;
                protected array $data;

                public function __construct(string $title, string $body, array $data)
                {
                    $this->title = $title;
                    $this->body = $body;
                    $this->data = $data;
                }

                public function via($notifiable)
                {

                    Log::info('via() called for FCM notification 2');
                    return [FcmChannel::class];
                }

                public function toFcm($notifiable): FcmMessage
                {
                    Log::info('toFcm() called', [
                        'title' => $this->title,
                        'body' => $this->body,
                        'data' => $this->data,
                    ]);

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

            Log::info('FCM notification sent successfully to token: ' . $fcmToken);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification: ' . $e->getMessage());
            Log::error($e);
            return false;
        } catch (\Throwable $e) {
            Log::error('Throwable caught during FCM send: ' . $e->getMessage());
            Log::error($e);
            return false;
        }
    }
}
