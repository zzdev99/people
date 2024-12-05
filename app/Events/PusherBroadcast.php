<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PusherBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $status;

    public function __construct($message, $status)
    {
        $this->message = $message;
        $this->status = $status;
    }

    public function broadcastOn()
    {
        return ['job-status'];
    }

    public function broadcastAs()
    {
        return 'job-status-event';
    }
}
