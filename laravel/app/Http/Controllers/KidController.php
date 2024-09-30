<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Inertia\Inertia;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ErrorMessagesHelper;

class KidController extends Controller
{
    public function index() //: Response
    {
        $kids = Kid::with(['places'])->get(); //Load kids with number of places each has

        // Add a new attribute for place IDs
        $kids->transform(function ($kid) {
            $kid->place_ids = $kid->places->pluck('id')->toArray(); // Collect place IDs
            return $kid;
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
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'email' => ['required', 'email', 'lowercase'],
            'wheelchair' => ['required', 'boolean'],
            'places' => [
                'array',

                function ($attribute, $value, $fail) {
                    // Iterate over places to check if all have 'Residência' as place_type
                    foreach ($value as $placeId) {
                        $place = Place::find($placeId);
                        if (!$place || $place->place_type !== 'Residência') {
                            $fail('Apenas moradas com tipo "Residência" podem ser associadas a crianças');
                        }
                    }
                }

            ],
        ], $customErrorMessages);
        
        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);

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

        $kidPlaces = $kid->places;                                                  //Given kid places

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
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'email' => ['required', 'email', 'lowercase'],
            'wheelchair' => ['required', 'boolean'],
            'addPlaces' => [
                'array',

                function ($attribute, $value, $fail) {
                    // Iterate over places to check if all have 'Residência' as place_type
                    foreach ($value as $placeId) {
                        $place = Place::find($placeId);
                        if (!$place || $place->place_type !== 'Residência') {
                            $fail('Apenas moradas com tipo "Residência" podem ser associadas a crianças');
                        }
                    }
                }

            ],
            'removePlaces' => 'array',
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);

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
}
