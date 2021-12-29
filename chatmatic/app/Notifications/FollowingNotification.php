<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Log;

class FollowingNotification extends Notification implements ShouldQueue {
    use Queueable;

    private $followee;
    private $follower;

    public function __construct($followee_uid, $follower_uid) {
        $this->followee = User::find($followee_uid);
        $this->follower = User::find($follower_uid);
    }

    public function via($notifiable) {
        return ['broadcast'];
    }

    public function toArray($notifiable) {
        return [
            'followee' => $this->followee,
            'follower' => $this->follower
        ];
    }

    public function toBroadcast($notifiable) {
        Log::info('Log followee: '. json_encode($this->followee));
        Log::info('Log follower: '. json_encode($this->follower));
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastType() {
        return 'user-followed';
    }
}
