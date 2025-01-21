<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Inertia\Inertia;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use App\Rules\KidPlaceTypeValidation;
use Illuminate\Support\Facades\Gate;

class KidController extends Controller
{
    public function index() //: Response
    {
        if(! Gate::allows('view-kid')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed kids page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $kids = Kid::with(['places'])->get(); //Load kids with number of places each has

        $places = Place::all();

        return Inertia::render('Kids/AllKids', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'kids' => $kids,
            'places' => $places
        ]);
    }

    public function showCreateKidForm()
    {
        if(! Gate::allows('create-kid')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed kid creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $kids = Kid::with(['places'])->get(); //Load kids with number of places each has

        // Add a new attribute for place IDs
        $kids->transform(function ($kid) {
            $kid->place_ids = $kid->places->pluck('id')->toArray(); // Collect place IDs
            return $kid;
        });

        $places = Place::where('place_type', 'Residência')->get();

        return Inertia::render('Kids/NewKid', ['kids' => $kids, 'places' => $places]);
    }

    public function createKid(Request $request)
    {
        if(! Gate::allows('create-kid')) {
            abort(403);
        }

        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => 'required',
            'wheelchair' => ['required', 'boolean'],
            'places' => [
                'array',
                new KidPlaceTypeValidation(),
            ],
        ], $customErrorMessages);
        
        $incomingFields['name'] = strip_tags($incomingFields['name']);

        $addPlaces = isset($incomingFields['places']) ? array_map('strip_tags', $incomingFields['places']) : [];

        DB::beginTransaction();
        try {
            $kid = Kid::create($incomingFields);
            $kid->places()->attach($addPlaces);
            
            DB::commit();

            Log::channel('user')->info('User created a kid', [
                'auth_user_id' => $this->loggedInUserId ?? null,
            ]);
    

            return redirect()->route('kids.index')->with('message', 'Criança com id ' . $kid->id . ' criada com sucesso!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('usererror')->error('Error creating kid', [
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('kids.index')->with('error', 'Houve um problema ao criar a criança. Tente novamente.');
        }
    }

    public function showEditKidForm(Kid $kid)
    {
        if(! Gate::allows('edit-kid')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed kid edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'kid_id' => $kid->id ?? null,
        ]);

        $kidPlaces = $kid->places;

        $associatedPlaceIds = $kid->places->pluck('id');
        $availablePlaces = Place::whereNotIn('id', $associatedPlaceIds)->where('place_type', 'Residência')->get();     //Places that dont belong to given kid

        return Inertia::render('Kids/EditKid', ['kid' => $kid, 'kidPlaces' => $kidPlaces, 'availablePlaces' => $availablePlaces]);
    }

    public function editKid(Kid $kid, Request $request)
    {
        if(! Gate::allows('edit-kid')) {
            abort(403);
        }

        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => 'required',
            'wheelchair' => ['required', 'boolean'],
            'addPlaces' => [
                'array',
                new KidPlaceTypeValidation(),
            ],
            'removePlaces' => 'array',
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);

        $addPlaces = isset($incomingFields['addPlaces']) ? array_map('strip_tags', $incomingFields['addPlaces']) : [];
        $removePlaces = isset($incomingFields['removePlaces']) ? array_map('strip_tags', $incomingFields['removePlaces']) : [];

        DB::beginTransaction();
        try {
            $kid->update($incomingFields);
            $kid->places()->attach($addPlaces);
            $kid->places()->detach($removePlaces);

            DB::commit();

            
            Log::channel('user')->info('User edited a kid', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'kid_id' => $kid->id ?? null,
            ]);

            return redirect()->route('kids.index')->with('message', 'Dados da criança #' . $kid->id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('usererror')->error('Error editing kid', [
                'kid_id' => $kid->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('kids.index')->with('error', 'Houve um problema ao atualizar os dados da criança com id ' . $kid->id . '. Tente novamente.');
        }
    }

    public function deleteKid($id)
    {
        if(! Gate::allows('delete-kid')) {
            abort(403);
        }

        try {
            $kid = Kid::findOrFail($id);
            $kid->delete();

            Log::channel('user')->info('User deleted a kid', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'kid_id' => $id ?? null,
            ]);

            return redirect()->route('kids.index')->with('message', 'Dados da criança com id ' . $id . ' apagados com sucesso!');
            
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting kid', [
                'kid_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('kids.index')->with('error', 'Houve um problema ao eliminar os dados da criança com id ' . $id . '. Tente novamente.');
        }
    }

    public function showKidContacts(Kid $kid)
    {
        if(! Gate::allows('view-kid')) {
            abort(403);
        }
        
        Log::channel('user')->info('User accessed kids contact page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'kid_id' => $kid->id  ?? null,
        ]);

        // Use 'load' to eager load the relationships on the already retrieved kid instance
        $kid->load('phoneNumbers', 'emails');
        
        return Inertia::render('Kids/KidContacts', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'kid' => $kid
        ]);
    }
}
