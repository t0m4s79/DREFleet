<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // $user->notify(new DocumentExpiryNotification($vehicle, $document));

    /**
     * Get logged in user notifications
     */
    public function index()
    {
        Log::channel('user')->info('User accessed notifications page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $notifications = Auth::user()->notifications->sortByDesc('created_at')->values();

        return Inertia::render('Notifications/AllNotifications', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'notifications' => $notifications,
        ]);
    }
    
    public function markAsRead(Notification $notification, Request $request)
    {
        try {
            if ($notification->user_id != Auth::user()->id) {
                throw new \InvalidArgumentException("Impossível marcar como lida. Esta notificação não pertence ao utilizador autenticado");
            }

            $notification->update([
                'is_read' => true,
            ]);

            Log::channel('user')->info('User marked notification as read', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'manager_id' => $notification->id ?? null,
            ]);

            return response()->json([
                'message' => 'Notificação marcada como lida com sucesso!',
                'status' => 'success'
            ], 200);
    

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 400);
    
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error marking notification as read', [
                'notification_id' => $notification->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Houve um problema ao marcar a notificação com id ' . $notification->id . ' como lida. Tente novamente.',
                'status' => 'error'
            ], 500);
        }
    }
    

    public function deleteNotification($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            Log::channel('user')->info('User deleted a notification', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'manager_id' => $id ?? null,
            ]);

            return redirect()->back()->with('success', 'Notificação apagada com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting notification', [
                'notification_id' => $notification->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('notifications.index')->with('error', 'Houve um problema ao apagar a notificação com id ' . $id . '. Tente novamente.');
        }
    }

    public function getUnreadCount() {
        $unreadCount = Auth::user()->notifications->where('is_read', '0')->count();

        return response()->json(['unread_count' => $unreadCount]);
    }
}
