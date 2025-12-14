<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class NotificationCenter extends Component
{
    use WithPagination;

    // Modal control (only for create)
    public $showCreateModal = false;

    // List view properties
    public $filter = 'all';

    // Create notification properties
    public $user_id = '';
    public $email = '';
    public $subject = '';
    public $message = '';
    public $priority = 'info';

    protected $paginationTheme = 'tailwind';
    protected $queryString = [
        'filter' => ['except' => 'all']
    ];

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|min:10',
        'priority' => 'required|in:info,important,urgent',
    ];

    protected $messages = [
        'user_id.required' => 'Debe seleccionar un destinatario',
        'user_id.exists' => 'El usuario seleccionado no existe',
        'subject.required' => 'El asunto es obligatorio',
        'subject.max' => 'El asunto no puede exceder 255 caracteres',
        'message.required' => 'El mensaje es obligatorio',
        'message.min' => 'El mensaje debe tener al menos 10 caracteres',
        'priority.required' => 'Debe seleccionar una prioridad',
        'priority.in' => 'La prioridad seleccionada no es válida',
    ];

    public function updated($property, $value)
    {
        if ($property === 'user_id' && $value) {
            $user = User::find($value);
            $this->email = $user ? $user->email : '';
        } elseif ($property === 'user_id' && !$value) {
            $this->email = '';
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

        // Lista de usuarios para el formulario de creación
        $users = User::orderBy('name')->get();

        return view('livewire.notifications.notification-center', [
            'notifications' => $notifications,
            'totalCount' => $totalCount,
            'unreadCount' => $unreadCount,
            'automaticCount' => $automaticCount,
            'manualCount' => $manualCount,
            'users' => $users,
        ]);
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);

        if ($notification && $notification->user_id === auth()->id() && !$notification->read_at) {
            $notification->markAsRead();
            
            // Dispatch to update dropdown
            $this->dispatch('notifications-updated');
        }
    }

    public function markAllAsRead()
    {
        $count = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

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
            $this->dispatch('notifications-updated');
        }
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->reset(['user_id', 'email', 'subject', 'message', 'priority']);
        $this->resetValidation();
    }

    public function sendNotification()
    {
        $this->validate();

        $sender = auth()->user();
        $greeting = 'Estimado profesor';

        Notification::create([
            'user_id' => $this->user_id,
            'notification_type' => 'direct_message',
            'priority' => empty($this->priority) ? 'info' : $this->priority,
            'is_automatic' => false,
            'title' => $this->subject,
            'message' => "{$greeting},\n\n{$this->message}\n\nAtentamente,\n{$sender->name} {$sender->last_name}",
            'data' => [
                'sender_id' => $sender->id,
                'sender_name' => "{$sender->name} {$sender->last_name}",
                'greeting' => $greeting,
            ],
        ]);

        session()->flash('success', 'Notificación enviada exitosamente');

        $this->closeCreateModal();
        
        // Dispatch to update dropdown
        $this->dispatch('notifications-updated');
    }
}
