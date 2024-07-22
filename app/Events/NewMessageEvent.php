<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $listeningPartyId;

    public $message;

    public function __construct($listeningPartyId, $message)
    {
        $this->listeningPartyId = $listeningPartyId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('listening-party.'.$this->listeningPartyId);
    }

    public function broadcastAs()
    {
        return 'new-message';
    }
}
