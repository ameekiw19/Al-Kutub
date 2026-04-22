<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Kitab;

class NewKitabAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $kitab;
    public $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(Kitab $kitab, $notification = null)
    {
        $this->kitab = $kitab;
        $this->notification = $notification ?: [
            'title' => 'Kitab Baru Tersedia!',
            'message' => "Kitab '{$kitab->judul}' oleh {$kitab->penulis} telah ditambahkan. Yuk baca sekarang!",
            'type' => 'new_kitab',
            'action_url' => "/kitab/{$kitab->id_kitab}",
            'created_at' => now()->toISOString(),
            'data' => [
                'kitab_id' => $kitab->id_kitab,
                'judul' => $kitab->judul,
                'penulis' => $kitab->penulis,
                'kategori' => $kitab->kategori,
                'cover' => $kitab->cover,
                'deskripsi' => substr($kitab->deskripsi, 0, 150) . '...'
            ]
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return [
            new Channel('new-kitab'), // Public channel for all users
            new PrivateChannel('user.' . auth()->id()), // Private channel for authenticated users
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'kitab.added';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'kitab' => [
                'id_kitab' => $this->kitab->id_kitab,
                'judul' => $this->kitab->judul,
                'penulis' => $this->kitab->penulis,
                'kategori' => $this->kitab->kategori,
                'bahasa' => $this->kitab->bahasa,
                'cover' => $this->kitab->cover,
                'deskripsi' => $this->kitab->deskripsi,
                'created_at' => $this->kitab->created_at->toISOString(),
            ],
            'notification' => $this->notification
        ];
    }
}
