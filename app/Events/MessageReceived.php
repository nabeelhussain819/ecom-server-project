<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('messages.' . $this->user->id);
    }

    public function broadcastWith()
    {
        return ['messages' => Message::where('sender_id', $this->user->id)
            ->orWhere('recipient_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)];
    }

    public function broadcastAs()
    {
        return 'MessageReceived';
    }

    public static function trigger(User $user)
    {
        try {
            event(new self($user));
        } catch (\Exception $ex) {
            Log::error(__CLASS__);
        }
    }
}
