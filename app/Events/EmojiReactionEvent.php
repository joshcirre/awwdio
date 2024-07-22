<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmojiReactionEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $listeningPartyId;

    public $emoji;

    public $userId;

    public function __construct($listeningPartyId, $emoji, $userId)
    {
        $this->listeningPartyId = $listeningPartyId;
        $this->emoji = $emoji;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new Channel('listening-party.'.$this->listeningPartyId);
    }

    public function broadcastAs()
    {
        return 'emoji-reaction';
    }

    public function broadcastWith()
    {
        return [
            'emoji' => $this->emoji,
            'userId' => $this->userId,
        ];
    }
}
