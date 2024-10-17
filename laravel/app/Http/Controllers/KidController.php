<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Inertia\Inertia;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ErrorMessagesHelper;
use App\Rules\KidPlaceTypeValidation;

class KidController extends Controller
{
    public function index() //: Response
    {
        $kids = Kid::with(['places'])->get(); //Load kids with number of places each has

        $kids->each(function ($kid) {
            $kid->created_at = \Carbon\Carbon::parse($kid->created_at)->format('d-m-Y H:i');
            $kid->updated_at = \Carbon\Carbon::parse($kid->updated_at)->format('d-m-Y H:i');
        });

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

            return redirect()->route('kids.index')->with('message', 'Criança com id ' . $kid->id . ' criada com sucesso!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->route('kids.index')->with('error', 'Houve um problema ao criar a criança. Tente novamente.');
        }
    }

    public function showEditKidForm(Kid $kid)
    {

        $kidPlaces = $kid->places;

        $associatedPlaceIds = $kid->places->pluck('id');
        $availablePlaces = Place::whereNotIn('id', $associatedPlaceIds)->where('place_type', 'Residência')->get();     //Places that dont belong to given kid

        return Inertia::render('Kids/EditKid', ['kid' => $kid, 'kidPlaces' => $kidPlaces, 'availablePlaces' => $availablePlaces]);
    }

    public function editKid(Kid $kid, Request $request)
    {
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

            return redirect()->route('kids.index')->with('message', 'Dados da criança #' . $kid->id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->route('kids.index')->with('error', 'Houve um problema ao atualizar os dados da criança com id ' . $kid->id . '. Tente novamente.');
        }
    }

    public function deleteKid($id)
    {
        try {
            $kid = Kid::findOrFail($id);
            $kid->delete();

            return redirect()->route('kids.index')->with('message', 'Dados da criança com id ' . $id . ' apagados com sucesso!');
            
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('kids.index')->with('error', 'Houve um problema ao eliminar os dados da criança com id ' . $id . '. Tente novamente.');
        }
    }

    public function showKidContacts(Kid $kid)
    {
        // Use 'load' to eager load the relationships on the already retrieved kid instance
        $kid->load('phoneNumbers', 'emails');

        // Format the fields for each accessory
        $kid->phoneNumbers->each(function ($phone) {
            $phone->created_at = \Carbon\Carbon::parse($phone->created_at)->format('d-m-Y H:i');
            $phone->updated_at = \Carbon\Carbon::parse($phone->updated_at)->format('d-m-Y H:i');
        }); 

        // Format the fields for each document
        $kid->emails->each(function ($email) {
            $email->created_at = \Carbon\Carbon::parse($email->created_at)->format('d-m-Y H:i');
            $email->updated_at = \Carbon\Carbon::parse($email->updated_at)->format('d-m-Y H:i');
        });
        
        return Inertia::render('Kids/KidContacts', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'kid' => $kid
        ]);
    }
}
