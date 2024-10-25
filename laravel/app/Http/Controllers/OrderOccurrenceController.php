<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Models\OrderOccurrence;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use App\Notifications\OrderOccurrenceNotification;

class OrderOccurrenceController extends Controller
{
    public function index()
    {
        Log::channel('user')->info('User accessed occurrences page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $occurrences = OrderOccurrence::with(['order.driver', 'order.vehicle'])->get();

        $occurrences->each(function ($occurrence) {
            $occurrence->order->expected_begin_date = \Carbon\Carbon::parse($occurrence->order->expected_begin_date )->format('d-m-Y');
        });

        return Inertia::render('OrderOccurrences/AllOrderOccurrences', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'occurrences' => $occurrences,
        ]);
    }

    public function showCreateOrderOccurrenceForm()
    {
        Log::channel('user')->info('User accessed occurrence creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $orders = Order::with(['driver', 'vehicle'])->get();

        return Inertia::render('OrderOccurrences/NewOrderOccurence', [
            'orders' => $orders,
        ]);
    }

    public function createOrderOccurrence(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'type' => ['required', Rule::in(['Manutenções','Reparações','Lavagens', 'Outros'])],
            'description' => ['required', 'string', 'max:500'],
            'order_id' => ['required', 'exists:orders,id'],
        ], $customErrorMessages);

        $incomingFields['description'] = strip_tags($incomingFields['description']);

        try {
            $occurrence = OrderOccurrence::create($incomingFields);

            $order = Order::findOrFail($incomingFields['order_id']);

            // Notify all users with the user_type 'Gestor'
            foreach (User::where('user_type', 'Gestor')->get() as $user) {
                $user->notify(new OrderOccurrenceNotification($order, $occurrence));
            }

            // Notify driver involved (if not null)
            $driver = User::find($order->driver_id);

            if ($driver) {
                $driver->notify(new OrderOccurrenceNotification($order, $occurrence));
            }

            Log::channel('user')->info('User created an occurrence', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'occurrence_id' => $occurrence->id ?? null,
            ]);

            return redirect()->route('orders.occurrences', $incomingFields['order_id'])->with('message', 'Ocorrência com id ' . $occurrence->id . ' pertencente ao ao pedido com id ' . $incomingFields['order_id'] . ' criada com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating occurrence', [
                'order_id' => $incomingFields['order_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('orders.occurrences', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao criar a ocorrência para o pedido com id ' . $incomingFields['order_id'] . '. Tente novamente.');
        }
    }

    public function showEditOrderOccurrenceForm(OrderOccurrence $orderOccurrence)
    {
        Log::channel('user')->info('User accessed occurrence edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'occurrence_id' => $orderOccurrence->id ?? null,
        ]);

        $orders = Order::with(['driver', 'vehicle'])->get();

        return Inertia::render('OrderOccurrences/EditOrderOccurrence', [
            'occurrence' => $orderOccurrence,
            'orders' => $orders,
        ]);
    }

    public function editOrderOccurrence(OrderOccurrence $orderOccurrence, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'type' => ['required', Rule::in(['Manutenções','Reparações','Lavagens', 'Outros'])],
            'description' => ['required', 'string', 'max:500'],
            'order_id' => ['required', 'exists:orders,id'],
        ], $customErrorMessages);

        $incomingFields['description'] = strip_tags($incomingFields['description']);

        try {
            $orderOccurrence->update($incomingFields);

            Log::channel('user')->info('User edited an occurrence', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'occurrence_id' => $orderOccurrence->id ?? null,
            ]);

            return redirect()->route('orders.occurrences', $incomingFields['order_id'])->with('message', 'Dados da ocorrência com id ' . $orderOccurrence->id . ' pertencente ao pedido com id ' . $incomingFields['order_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing occurrence', [
                'order_occurrence_id' => $incomingFields['order_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('orders.occurrences', $incomingFields['order_id'])->with('error', 'Houve um problema ao atualizar os dados da ocorrência com id ' . $orderOccurrence->id . ' pertencente ao pedido com id ' . $incomingFields['order_id'] . '. Tente novamente.');
        }
    }

    public function deleteOrderOccurrence($id)
    {
        try {
            $orderOccurrence = OrderOccurrence::findOrFail($id);
            $orderId = $orderOccurrence->order->id;
            $orderOccurrence->delete();

            Log::channel('user')->info('User deleted an occurrence', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'occurrence_id' => $id ?? null,
            ]);
    
            return redirect()->route('orders.occurrences', $orderId)->with('message', 'Occorrência com id ' . $id . ' eliminada com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting occurrence', [
                'order_occurrence_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('occurrences.index')->with('error', 'Houve um problema ao apagar a occorrência com id ' . $id . '. Tente novamente.');
        }
    }
}
