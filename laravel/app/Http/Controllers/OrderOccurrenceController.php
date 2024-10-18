<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderOccurrence;
use Illuminate\Validation\Rule;
use App\Helpers\ErrorMessagesHelper;

class OrderOccurrenceController extends Controller
{
    public function index()
    {
        $occurrences = OrderOccurrence::with(['order.driver', 'order.vehicle'])->get();

        $occurrences->each(function ($occurrence) {
            $occurrence->order->expected_begin_date = \Carbon\Carbon::parse($occurrence->order->expected_begin_date )->format('d-m-Y');
            $occurrence->created_at = \Carbon\Carbon::parse($occurrence->created_at)->format('d-m-Y H:i');
            $occurrence->updated_at = \Carbon\Carbon::parse($occurrence->updated_at)->format('d-m-Y H:i');
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

            return redirect()->route('orders.occurrences', $incomingFields['order_id'])->with('message', 'Ocorrência com id ' . $occurrence->id . ' pertencente ao ao pedido com id ' . $incomingFields['order_id'] . ' criada com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.occurrences', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao criar a ocorrência para o pedido com id ' . $incomingFields['order_id'] . '. Tente novamente.');
        }
    }

    public function showEditOrderOccurrenceForm(OrderOccurrence $orderOccurrence)
    {
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

            return redirect()->route('orders.occurrences', $incomingFields['order_id'])->with('message', 'Dados da ocorrência com id ' . $orderOccurrence->id . ' pertencente ao pedido com id ' . $incomingFields['order_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.occurrences', $incomingFields['order_id'])->with('error', 'Houve um problema ao atualizar os dados da ocorrência com id ' . $orderOccurrence->id . ' pertencente ao pedido com id ' . $incomingFields['order_id'] . '. Tente novamente.');
        }
    }

    public function deleteOrderOccurrence($id)
    {
        try {
            $orderOccurrence = OrderOccurrence::findOrFail($id);
            $orderId = $orderOccurrence->order->id;
            $orderOccurrence->delete();
    
            return redirect()->route('orders.occurrences', $orderId)->with('message', 'Occorrência com id ' . $id . ' eliminada com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.occurrences', $orderId)->with('error', 'Houve um problema ao apagar a occorrência com id ' . $id . '. Tente novamente.');
        }
    }
}
