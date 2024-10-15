<?php

namespace App\Http\Controllers;

use App\Models\OrderStop;
use Illuminate\Http\Request;
use App\Helpers\ErrorMessagesHelper;

// This class doesn't need to implement transactions because it is only
// called by the order controller which already implements its own
class OrderStopController extends Controller
{
    public function createOrderStop(Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'expected_arrival_date' => ['required', 'date'],
            'stop_number' => ['required', 'integer', 'min:0'],
            'order_id' => ['required','exists:orders,id'],
            'place_id' => ['required','exists:places,id'],
            'time_from_previous_stop' => ['required', 'numeric', 'min:0'],
            'distance_from_previous_stop' => ['required', 'numeric', 'min:0'],
            'kid_id' => ['nullable', 'exists:kids,id'],
        ], $customErrorMessages);

        $incomingFields['expected_arrival_date'] = $incomingFields['expected_arrival_date'] ?? null;
        
        try {
            $orderStop = OrderStop::create([
                'expected_arrival_date' => $incomingFields['expected_arrival_date'],
                'stop_number' => $incomingFields['stop_number'],
                'time_from_previous_stop' => $incomingFields['time_from_previous_stop'],
                'distance_from_previous_stop' => $incomingFields['distance_from_previous_stop'],
                'order_id' => $incomingFields['order_id'],
                'place_id' => $incomingFields['place_id'],
            ]);

            if (isset($incomingFields['kid_id'])) {
                $orderStop->kids()->attach($incomingFields['kid_id'], [
                    'place_id' => $incomingFields['place_id'],
                    'order_stop_id' => $orderStop->id,
                ]);
            }

            // return redirect()->route('orders.index')->with('message', 'Paragem com id ' . $orderStop->id . ' criada com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            // return redirect()->route('orders.index')->with('error', 'Houve um problema ao criar a paragem. Tente novamente.');
        }
    }

    // TODO: WHAT ELSE SHOULD THIS EDIT INCLUDE?
    public function editOrderStop(OrderStop $orderStop, Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'expected_arrival_date' => ['required', 'date'],
        ], $customErrorMessages);

        try {
            $orderStop->update([
                'expected_arrival_date' => $incomingFields['expected_arrival_date'],
            ]);

            // return redirect()->route('orders.index')->with('message', 'Dados da paragem com ' . $orderStop->id . ' atualizados com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            // return redirect()->route('orders.index')->with('error', 'Houve um problema ao editar os dados do pedido com id ' . $order->id . '. Tente novamente.');
        }
    }

    public function deleteOrderStop($id)
    {
        try {
            $orderStop = OrderStop::findOrFail($id);
            $orderStop->delete();

            // return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $orderStop->id . ' apagado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            // return redirect()->route('orders.index')->with('error', 'Houve um problema ao apagar o pedido com id ' . $order->id . '. Tente novamente.');
        }
    }
    
    // To be used by the drivers/technicians when they reach a waypoint
    public function orderStopReached(OrderStop $orderStop, Request $request) 
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'actual_arrival_date' => ['required', 'date'],
        ], $customErrorMessages);
        
        try {
            $orderStop->update([
                'actual_arrival_date' => $incomingFields['actual_arrival_date'],
            ]);

            // return redirect()->route('orders.index')->with('message', 'Data em que chegou à paragem com id ' . $orderStop->id . ' definida com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            // return redirect()->route('orders.index')->with('error', 'Houve um problema ao definir a data em que chegou à paragem com id ' . $orderStop->id);
        }
    }
}
