<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public $showDropdown = false;
    public $notifications = [];
    public $unreadCount = 0;

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function goToNotification($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification && $notification->user_id === auth()->id()) {
            // Cerrar dropdown
            $this->showDropdown = false;

            // Redirigir al centro de notificaciones con la notificación seleccionada
            return $this->redirect(route('notifications.index', ['selected' => $notificationId]), navigate: true);
        }
    }

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
