<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class NotificationController extends Controller
{
    // $user->notify(new DocumentExpiryNotification($vehicle, $document));

    /**
     * Get logged in user notifications
     */
    public function index()
    {
        $notifications = Auth::user()->notifications->sortByDesc('created_at')->values();

        return Inertia::render('Notifications/AllNotifications', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'notifications' => $notifications,
        ]);
    }
    
    public function markAsRead(Notification $notification, Request $request) {
        try {
            if ($notification->user_id != Auth::user()->id) {
                throw new \InvalidArgumentException("Impossível marcar como lida. Esta notificação não pertence ao utilizador autenticado");
            }

            $notification->update([
                'is_read' => true,
            ]);

            return redirect()->route('notifications.index')->with('message', 'Notificação com id ' . $notification->id . ' marcada como lida com sucesso!');

        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('notifications.index')->with('error', 'Houve um problema ao marcar a notificação com id ' . $notification->id . ' como lida. Tente novamente.');
        }
    }

    public function deleteNotification($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return redirect()->back()->with('success', 'Notificação apagada com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('notifications.index')->with('error', 'Houve um problema ao apagar a notificação com id ' . $notification->id . '. Tente novamente.');
        }
    }

    public function getUnreadCount() {
        $unreadCount = Auth::user()->notifications->where('is_read', '0')->count();

        return response()->json(['unread_count' => $unreadCount]);
    }
}
