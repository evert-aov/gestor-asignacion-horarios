<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public $showDropdown = false;
    public $notifications = [];
    public $unreadCount = 0;

    protected $listeners = ['notifications-updated' => '$refresh'];

    public function render()
    {
        $this->notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $this->unreadCount = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return view('livewire.notifications.notification-dropdown');
    }
}
