<?php
// App/Events/UserTyping.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $userName;

    public function __construct($conversationId, $userName)
    {
        $this->conversationId = $conversationId;
        $this->userName = $userName;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->conversationId);
    }
}