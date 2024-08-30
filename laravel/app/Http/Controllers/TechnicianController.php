<?php

namespace App\Http\Controllers;

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
            'technicians' => $technicians,
        ]);
    }

    public function showCreateTechnicianForm() {
        $users = User::where('user_type','Nenhum')->get();
        
        $kidsWithPriority1Ids = DB::table('kid_user')
        ->where('priority', 1)
        ->pluck('kid_id')
        ->toArray();

        $kidsNotWithPriority1 = Kid::whereNotIn('id', $kidsWithPriority1Ids)->get();

        return Inertia::render('Technicians/NewTechnician', ['users' => $users, 'priority1AvailableKids' => $kidsNotWithPriority1,'priority2AvailableKids' =>  Kid::all(), '']);
    }

    //TODO: PRIORITY 1 VERIFICATION
    public function createTechnician(Request $request) {
        $incomingFields = $request->validate([
            'id' => ['required','exists:users,id'],
            'kidsList1' => 'array',
            'kidsList2' => 'array',
        ]);

        $incomingFields['id'] = strip_tags($incomingFields['id']);
        
        $user = User::find($incomingFields['id']);
        
        if ($user->user_type != 'Nenhum') {
            return redirect('/technicians')->with('error', 'Somente utilizadores de tipo "Nenhum" podem ser convertidos em técnicos.');
        }

        $kidsList1 = isset($incomingFields['kidsList1']) ? array_map('strip_tags', $incomingFields['kidsList1']) : [];
        $kidsList2 = isset($incomingFields['kidsList2']) ? array_map('strip_tags', $incomingFields['kidsList2']) : [];

        try{
            $user->update([
                'user_type' => "Técnico",
            ]);

            $user->kids()->attach($kidList1, ['priority' => 1]);
            $user->kids()->attach($kidsList2, ['priority' => 2]);

            return redirect('/technicians')->with('message', 'Técnico/a criado/a com sucesso!');
        } catch (\Exception $e) {
            return redirect('technicians')->with('error', 'Houve um problema ao criar o técnico. Tente novamente.');
        }  
    }

    //TODO: PRIORITY 1 VERIFICATION
    public function editTechnician(User $user, Request $request) {
        $incomingFields = $request->validate([
            'id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'status' => 'required',
            'addPriority1' => 'array',
            'removePriority1' => 'array',
            'addPriority2' => 'array',
            'removePriority2' => 'array',
        ]);

        $incomingFields['heavy_license'] = strip_tags($incomingFields['heavy_license']);
        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);
        $incomingFields['status'] = strip_tags($incomingFields['status']);

        $addPriority1 = isset($incomingFields['addPriority1']) ? array_map('strip_tags', $incomingFields['addPriority1']) : [];
        $removePriority1 = isset($incomingFields['removePriority1']) ? array_map('strip_tags', $incomingFields['removePriority1']) : [];

        $addPriority2 = isset($incomingFields['addPriority2']) ? array_map('strip_tags', $incomingFields['addPriority2']) : [];
        $removePriority2 = isset($incomingFields['removePriority2']) ? array_map('strip_tags', $incomingFields['removePriority2']) : [];
        
        try {
            $user->update([
                'name' => $incomingFields['name'],
                'email' => $incomingFields['email'],
                'phone' => $incomingFields['phone'],
                'status_code' => $incomingFields['status'],
            ]);

            $user->kids()->attach($addPriority1, ['priority' => 1]);
            $user->kids()->attach($addPriority2, ['priority' => 2]);

            $user->kids()->detach($removePriority1);
            $user->kids()->detach($removePriority2);

            return redirect('/drivers')->with('message', 'Dados do/a Condutor/a atualizados com sucesso!');
        }  catch (\Exception $e) {
            return redirect()->back()->with('error', 'Houve um problema ao editar os dados da criança. Tente novamente mais tarde.');
        }
    }

    public function showEditScreen(User $user) {
        $kidsWithPriority1Ids = DB::table('kid_user')
        ->where('priority', 1)
        ->pluck('kid_id')
        ->toArray();

        $kidsNotWithPriority1 = Kid::whereNotIn('id', $kidsWithPriority1Ids)->get();

        $userKids = $user->kids()
        ->select('kids.id', 'kid_user.priority')
        ->get()
        ->map(function ($kid) {
            return [
                'id' => $kid->id,
                'priority' => $kid->pivot->priority,
            ];
        });

        return Inertia::render('Technicians/Edit', [
            'technician' => $user,
            'associatedKids' => $userKids,
            'addPriority1' => $kidsNotWithPriority1,
            'addPriority2' => Kid::all(),
        ]);
    }

    public function deleteTechnician($id) {
        $user = User::findOrFail($id);
        $user->update([
            'user_type' => "Nenhum",
        ]);

        $user->kids()->detach();
        
        return redirect('/technicians');
    }
}
