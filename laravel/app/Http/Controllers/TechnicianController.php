<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use Illuminate\Support\Facades\Gate;

class TechnicianController extends Controller
{
    public function index()
    {
        if(! Gate::allows('view-user')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed technicians page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

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
        if(! Gate::allows('create-user')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed technician creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

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
        if(! Gate::allows('create-user')) {
            abort(403);
        }

        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'id' => [
                'required', 
                'exists:users,id',
                
                function ($attribute, $value, $fail) use ($request) {
                    $user = User::find($value);
        
                    if ($user && $user->user_type != 'Nenhum') {
                        $fail('Somente utilizadores de tipo "Nenhum" podem ser convertidos em técnicos');
                    }
                },

            ],
        ], $customErrorMessages);

        $user = User::find($incomingFields['id']);

        try {
            $user->update([
                'user_type' => "Técnico",
            ]);

            Log::channel('user')->info('User created a technician', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'technician_id' => $user->id ?? null,
            ]);

            return redirect()->route('technicians.index')->with('message', 'Técnico/a com id ' . $user->id . ' criado/a com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating technician', [
                'user_id' => $incomingFields['user_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('technicians.index')->with('error', 'Houve um problema ao adicionar o utilizador com id ' . $user->id . ' à lista de técnicos. Tente novamente.');
        }
    }

    public function showEditTechnicianForm(User $user)
    {
        if(! Gate::allows('edit-user')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed technician edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'technician_id' => $user->id ?? null,
        ]);

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
        if(! Gate::allows('edit-user')) {
            abort(403);
        }

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

            Log::channel('user')->info('User edited a technician', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'technician_id' => $user->id ?? null,
            ]);

            return redirect()->route('technicians.index')->with('message', 'Dados do/a técnico/a com id ' . $user->id . ' atualizados com sucesso!');
            
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing technician', [
                'route_id' => $user->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('technicians.index')->with('error', 'Houve um problema ao atualizar os dados do técnico com id ' . $user->id . '. Tente novamente.');
        }
    }

    public function deleteTechnician($id)
    {
        if(! Gate::allows('delete-user')) {
            abort(403);
        }

        try {
            $user = User::findOrFail($id);
            $user->update([
                'user_type' => "Nenhum",
            ]);

            Log::channel('user')->info('User deleted a technician', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'technician_id' => $id ?? null,
            ]);

            return redirect()->route('technicians.index')->with('message', 'Utilizador com id ' . $id . ' retirado da lista de técnicos com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting technician', [
                'route_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('technicians.index')->with('error', 'Houve um problema ao retirar o utilizador com id ' . $id . ' da lista de técnicos. Tente novamente.');
        }
    }
}
