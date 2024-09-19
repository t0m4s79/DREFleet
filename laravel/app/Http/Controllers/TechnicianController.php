<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorMessagesHelper;
use App\Models\Kid;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TechnicianController extends Controller
{
    public function index()
    {
        // Retrieve all technicians with their related kids, including the pivot priority
        $technicians = User::where('user_type', 'Técnico')
        ->with(['kids' => function ($query) {
            $query->withPivot('priority');
        }])
        ->get();

        // Transform the collection to add the priority arrays
        $technicians->transform(function ($technician) {
            // Filter kids by priority 1
            $technician->priority_1 = $technician->kids->filter(function ($kid) {
                return $kid->pivot->priority == 1;
            })->pluck('id')->toArray();

            // Filter kids by priority 2
            $technician->priority_2 = $technician->kids->filter(function ($kid) {
                return $kid->pivot->priority == 2;
            })->pluck('id')->toArray();

            return $technician;
        });

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

        $kidsWithPriority1Ids = DB::table('kid_user')
            ->where('priority', 1)
            ->pluck('kid_id')
            ->toArray();

        $kidsNotWithPriority1 = Kid::whereNotIn('id', $kidsWithPriority1Ids)->get();

        return Inertia::render('Technicians/NewTechnician', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'users' => $users,
            'priority1AvailableKids' => $kidsNotWithPriority1,
            'priority2AvailableKids' =>  Kid::all()
        ]);
    }

    public function createTechnician(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'id' => ['required', 'exists:users,id'],
            'kidsList1' => 'array',
            'kidsList2' => 'array',
        ], $customErrorMessages);

        $incomingFields['id'] = strip_tags($incomingFields['id']);

        $user = User::find($incomingFields['id']);

        if ($user->user_type != 'Nenhum') {
            return redirect('/technicians')->with('error', 'Somente utilizadores de tipo "Nenhum" podem ser convertidos em técnicos.');
        }

        $kidsList1 = isset($incomingFields['kidsList1']) ? array_map('strip_tags', $incomingFields['kidsList1']) : [];
        $kidsList2 = isset($incomingFields['kidsList2']) ? array_map('strip_tags', $incomingFields['kidsList2']) : [];

        $conflictingAdds = array_intersect($kidsList1, $kidsList2);

        if (!empty($conflictingAdds)) {
            return redirect()->back()->with('error', 'Não pode adicionar as mesmas crianças em 2 prioridades diferentes.');
        }

        try {
            $user->update([
                'user_type' => "Técnico",
            ]);

            $user->kids()->attach($kidsList1, ['priority' => 1]);
            $user->kids()->attach($kidsList2, ['priority' => 2]);

            return redirect('/technicians')->with('message', 'Técnico/a criado/a com sucesso!');
        } catch (\Exception $e) {
            return redirect('technicians')->with('error', 'Houve um problema ao criar o técnico. Tente novamente.');
        }
    }

    public function showEditTechnicianForm(User $user)
    {
        $kidsWithPriority1Ids = DB::table('kid_user')
            ->where('priority', 1)
            ->pluck('kid_id')
            ->toArray();

        $kidsNotWithPriority1 = Kid::whereNotIn('id', $kidsWithPriority1Ids)->get();

        $userKids = $user->kids()
            ->select('kids.id', 'kids.name', 'kid_user.priority')
            ->get()
            ->map(function ($kid) {
                return [
                    'id' => $kid->id,
                    'name' => $kid->name,
                    'priority' => $kid->pivot->priority,
                ];
            });

        return Inertia::render('Technicians/Edit', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'technician' => $user,
            'associatedKids' => $userKids,
            'addPriority1' => $kidsNotWithPriority1,
            'addPriority2' => Kid::all(),               //TODO: only kids not already associated with user
        ]);
    }

    public function editTechnician(User $user, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();
        
        $incomingFields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email'],
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'status' => 'required',
            'addPriority1' => 'array',
            'removePriority1' => 'array',
            'addPriority2' => 'array',
            'removePriority2' => 'array',
            'changePriority' => 'array',
        ], $customErrorMessages);
        
        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);
        $incomingFields['status'] = strip_tags($incomingFields['status']);

        $addPriority1 = isset($incomingFields['addPriority1']) ? array_map('strip_tags', $incomingFields['addPriority1']) : [];
        $removePriority1 = isset($incomingFields['removePriority1']) ? array_map('strip_tags', $incomingFields['removePriority1']) : [];

        $addPriority2 = isset($incomingFields['addPriority2']) ? array_map('strip_tags', $incomingFields['addPriority2']) : [];
        $removePriority2 = isset($incomingFields['removePriority2']) ? array_map('strip_tags', $incomingFields['removePriority2']) : [];

        $changePriority = isset($incomingFields['changePriority']) ? array_map('strip_tags', $incomingFields['changePriority']) : [];


        //CANT CHANGE AND REMOVE A PRIORITY AT THE SAME TIME
        if (!empty(array_intersect($changePriority, array_merge($removePriority1, $removePriority2)))) {
            return redirect()->back()->with('error', 'Não pode remover uma prioridade de uma criança e ao mesmo tempo alterá-la. Por favor, verifique.');
        }

        //CANT ADD A KID TWICE TO THE SAME TECHNICIAN WITH 2 DIFFERENT PRIORITIES
        if (array_intersect($addPriority1,$addPriority2)) {
            return redirect()->back()->with('error', 'Não pode adicionar as mesmas crianças em 2 prioridades diferentes.');
        }

        try {
            $user->update([
                'name' => $incomingFields['name'],
                'email' => $incomingFields['email'],
                'phone' => $incomingFields['phone'],
                'status' => $incomingFields['status'],
            ]);

            
            $user->kids()->attach($addPriority1, ['priority' => 1]);
            $user->kids()->attach($addPriority2, ['priority' => 2]);

            $user->kids()->detach($removePriority1);
            $user->kids()->detach($removePriority2);

            foreach ($changePriority as $kidId) {
                $currentPriority = $user->kids()->where('kid_id', $kidId)->first()->pivot->priority;
                $newPriority = ($currentPriority == 1) ? 2 : 1; // Switch priority
                $user->kids()->updateExistingPivot($kidId, ['priority' => $newPriority]);
            }

            return redirect('/technicians')->with('message', 'Dados do/a técnico/a atualizados com sucesso!');
        } catch (\Exception $e) {
            return redirect('/technicians')->with('error', 'Houve um problema ao editar os dados do técnico. Tente novamente.');
        }
    }

    public function deleteTechnician($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'user_type' => "Nenhum",
            ]);

            $user->kids()->detach();


            return redirect('/technicians')->with('message', 'Utilizador retirado da lista de técnicos com sucesso!');

        } catch (\Exception $e) {
            return redirect('/technicians')->with('error', 'Houve um problema ao retirar o utilizador da lista de técnicos. Tente novamente.');
        }
    }
}
