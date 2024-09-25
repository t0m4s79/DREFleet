<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;

class TechnicianController extends Controller
{
    public function index()
    {
        // Retrieve all technicians with their related kids, including the pivot priority
        $technicians = User::where('user_type', 'Técnico')->get();

        return Inertia::render('Technicians/AllTechnicians', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'technicians' => $technicians,
        ]);
    }

    public function showCreateTechnicianForm()
    {
        $users = User::where('user_type', 'Nenhum')->get();

        return Inertia::render('Technicians/NewTechnician', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'users' => $users,
        ]);
    }

    public function createTechnician(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'id' => ['required', 'exists:users,id'],
        ], $customErrorMessages);

        $user = User::find($incomingFields['id']);

        if ($user->user_type != 'Nenhum') {
            return redirect('/technicians')->with('error', 'Somente utilizadores de tipo "Nenhum" podem ser convertidos em técnicos.');
        }

        try {
            $user->update([
                'user_type' => "Técnico",
            ]);

            return redirect()->route('technicians.index')->with('message', 'Técnico/a com id ' . $user->id . ' criado/a com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('technicians.index')->with('error', 'Houve um problema ao adicionar o utilizador com id ' . $user->id . ' à lista de técnicos. Tente novamente.');
        }
    }

    public function showEditTechnicianForm(User $user)
    {
        return Inertia::render('Technicians/EditTechnician', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'technician' => $user,
        ]);
    }

    public function editTechnician(User $user, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();
        
        $incomingFields = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase'],
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'status' => ['required', Rule::in(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido'])],
        ], $customErrorMessages);
        
        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);

        try {
            $user->update([
                'name' => $incomingFields['name'],
                'email' => $incomingFields['email'],
                'phone' => $incomingFields['phone'],
                'status' => $incomingFields['status'],
            ]);

            return redirect()->route('technicians.index')->with('message', 'Dados do/a técnico/a com id ' . $user->id . ' atualizados com sucesso!');
            
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('technicians.index')->with('error', 'Houve um problema ao atualizar os dados do técnico com id ' . $user->id . '. Tente novamente.');
        }
    }

    public function deleteTechnician($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'user_type' => "Nenhum",
            ]);

            return redirect()->route('technicians.index')->with('message', 'Utilizador com id ' . $id . ' retirado da lista de técnicos com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('technicians.index')->with('error', 'Houve um problema ao retirar o utilizador com id ' . $id . ' da lista de técnicos. Tente novamente.');
        }
    }
}
