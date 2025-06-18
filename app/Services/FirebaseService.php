<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Google\Client as Google_Client;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->createMessaging();
    }

    function send_notify_to_topic($message, $topic) {
        $url = "https://fcm.googleapis.com/v1/projects/webliks-dashboard/messages:send";
    
        // Prepare the data for topic messaging
        $data = [
            "message" => [
                "topic" => $topic,  // Change from "token" to "topic"
                "notification" => [
                    "title" => $message->title,
                    "body" => $message->message,
                    // "image" => src($message->image_url),
                ],
                "android" => [
                    "priority" => "high",
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "default",
                        ],
                    ],
                ],
                "webpush" => [
                    "headers" => [
                        "Urgency" => "high",
                    ],
                ],
            ],
        ];
    
        // Send the request using OAuth 2.0 Bearer token
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' .$this->getAccessToken(),
            'Content-Type' => 'application/json',
        ])->post($url, $data);
    
        // Log the request and response
        \Log::alert(json_encode($data));
        \Log::alert($response->body());
    }
    
    static function getAccessToken() {
        $serviceAccount = json_decode(file_get_contents(config('firebase.credentials')), true);
        
        $client = new \Google_Client();
        $client->setAuthConfig($serviceAccount);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    
        $token = $client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }
    

    public function sendNotificationToTopics(array $topics, string $title, string $body)
    {
        // Create the notification
        $notification = Notification::create($title, $body);

        // Prepare responses array to capture responses for each topic
        $responses = [];

        $androidConfig = [
            'priority' => 'high',
            'notification' => [
                'sound' => 'default', // Ensure notification has sound
                'visibility' => 'public', // Control notification visibility
            ],
        ];

        foreach ($topics as $topic) {
            // Create a message for the topic
            $topic = str_replace(['/', '@', '.', '#', '$', '[', ']'], '_', $topic);
            // $message = CloudMessage::new()
            //     ->withNotification($notification)
            //     ->withAndroidConfig($androidConfig)
            //     ->withTarget('topic', $topic);  // Use 'topic' target

            // \Log::info($topic);
            // \Log::info($title);
            // \Log::info($body);
            // // Send the message and store the response
            // $responses[] = $this->messaging->send($message);
            $this->send_notify_to_topic((object) ['title' => $title, 'message' => $body], $topic);
        }

        return $responses;
    }
}
