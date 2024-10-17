<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Inertia\Inertia;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\ErrorMessagesHelper;
use MatanYadaev\EloquentSpatial\Objects\Point;

class PlaceController extends Controller
{
    public function index()
    {
        $places = Place::with(['kids'])->get(); //Load kids with number of places each has

        $places->each(function ($place) {
            $place->created_at = \Carbon\Carbon::parse($place->created_at)->format('d-m-Y H:i');
            $place->updated_at = \Carbon\Carbon::parse($place->updated_at)->format('d-m-Y H:i');
        });

        return Inertia::render('Places/AllPlaces', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'places' => $places
        ]);
    }

    public function showCreatePlaceForm()
    {
        return Inertia::render('Places/NewPlace');
    }

    public function createPlace(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'known_as' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{0,15}$/'],
            'longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{0,15}$/'],
            'place_type' => ['required', Rule::in(['Residência', 'Escola', 'Outros'])],            
        ], $customErrorMessages);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);

        try {
            $coordinates = new Point($incomingFields['latitude'], $incomingFields['longitude']);

            $place = Place::create([
                'address' => $incomingFields['address'],
                'known_as' => $incomingFields['known_as'],
                'coordinates' => $coordinates,
                'place_type' => $incomingFields['place_type'],
            ]);

            return redirect()->route('places.index')->with('message', 'Morada com id ' . $place->id . ' criada com sucesso!');
       
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('places.index')->with('error', 'Houve um problema ao criar a morada. Tente novamente.');
        }
    }

    public function showEditPlaceForm(Place $place)
    {
        $kids = Kid::all();
        return Inertia::render('Places/EditPlace', ['place' => $place, 'kids' => $kids]);
    }

    public function editPlace(Place $place, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'known_as' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{0,15}$/'],
            'longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{0,15}$/'],
            'place_type' => ['required', Rule::in(['Residência', 'Escola', 'Outros'])],            
        ], $customErrorMessages);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);

        try {
            $coordinates = new Point($incomingFields['latitude'], $incomingFields['longitude']);

            $place->update([
                'address' => $incomingFields['address'],
                'known_as' => $incomingFields['known_as'],
                'coordinates' => $coordinates,
                'place_type' => $incomingFields['place_type'],
            ]);

            return redirect()->route('places.index')->with('message', 'Dados da morada com id ' . $place->id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('places.index')->with('error', 'Houve um problema ao atualizar os dados da morada com id ' . $place->id . '. Tente novamente.');
        }
    }

    public function deletePlace($id)
    {
        try {
            $place = Place::findOrFail($id);
            $place->delete();

            return redirect()->route('places.index')->with('message', 'Morada com id ' . $id . ' apagada com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('places.index')->with('error', 'Houve um problema ao apagar a morada com id ' . $id . '. Tente novamente.');
        }
    }
}
