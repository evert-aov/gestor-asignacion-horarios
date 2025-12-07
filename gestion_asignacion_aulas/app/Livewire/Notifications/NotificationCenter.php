<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class NotificationCenter extends Component
{
    use WithPagination;

    public $filter = 'all';
    public $showModal = false;
    public $selectedNotification = null;
    public $selectedNotificationId = null;

    protected $paginationTheme = 'tailwind';
    protected $queryString = [
        'filter' => ['except' => 'all'],
        'selected' => ['except' => '']
    ];

    public function mount()
    {
        // Si viene un ID de notificación seleccionada desde el dropdown
        $selectedId = request()->query('selected');
        if ($selectedId) {
            $this->selectedNotificationId = $selectedId;
            $this->viewNotification($selectedId);
        }
    }

    public function hydrate()
    {
        // Verificar si hay un selected en la URL después de cada request
        $selectedId = request()->query('selected');
        if ($selectedId && !$this->showModal) {
            $this->viewNotification($selectedId);
        }
    }

    public function render(): View
    {
        $query = Notification::where('user_id', auth()->id())
            ->with('user')
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        switch ($this->filter) {
            case 'unread':
                $query->unread();
                break;
            case 'automatic':
                $query->automatic();
                break;
            case 'manual':
                $query->manual();
                break;
            case 'urgent':
                $query->byPriority('urgent');
                break;
            case 'important':
                $query->byPriority('important');
                break;
        }

        $notifications = $query->paginate(15);

        // Contadores
        $totalCount = Notification::where('user_id', auth()->id())->count();
        $unreadCount = Notification::where('user_id', auth()->id())->unread()->count();
        $automaticCount = Notification::where('user_id', auth()->id())->automatic()->count();
        $manualCount = Notification::where('user_id', auth()->id())->manual()->count();

        return view('livewire.notifications.notification-center', [
            'notifications' => $notifications,
            'totalCount' => $totalCount,
            'unreadCount' => $unreadCount,
            'automaticCount' => $automaticCount,
            'manualCount' => $manualCount,
        ]);
    }

    public function viewNotification($notificationId)
    {
        $this->selectedNotification = Notification::find($notificationId);

        if ($this->selectedNotification && $this->selectedNotification->user_id === auth()->id()) {
            $this->showModal = true;

            // Marcar como leída si no lo está
            if (!$this->selectedNotification->read_at) {
                $this->selectedNotification->markAsRead();
            }
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedNotification = null;
        $this->selectedNotificationId = null;
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', auth()->id())
            ->first();

        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notification-read');
        }
    }

    public function markAllAsRead()
    {
        $service = new NotificationService();
        $count = $service->markAllAsRead(auth()->user());

        session()->flash('success', "Se marcaron {$count} notificaciones como leídas");
        $this->dispatch('notifications-updated');
    }

    public function deleteNotification($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', auth()->id())
            ->first();

        if ($notification) {
            $notification->delete();
            session()->flash('success', 'Notificación eliminada exitosamente');
            $this->closeModal();
            $this->dispatch('notifications-updated');
        }
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function getUnreadCountProperty()
    {
        return Notification::where('user_id', auth()->id())->unread()->count();
    }
}
