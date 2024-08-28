<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use App\Models\Place;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index() //: Response
    {
        $places = Place::with(['kids'])->get(); //Load kids with number of places each has

        // Add a new attribute for place IDs
        $places->transform(function ($place) {
            $place->kid_ids = $place->kids->pluck('id')->toArray(); // Collect place IDs
            return $place;
        });

        return Inertia::render('Places/AllPlaces', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'places' => $places
        ]);
    }

    //TODO: more verification in each field and frontend verification messages!!!
    public function createPlace(Request $request)
    {
        $incomingFields = $request->validate([
            'address' => 'required',
            'known_as' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);
        $incomingFields['latitude'] = strip_tags($incomingFields['latitude']);
        $incomingFields['longitude'] = strip_tags($incomingFields['longitude']);

        try {
            Place::create($incomingFields);
            return redirect('/places')->with('message', 'Morada criada com sucesso!');;
        } catch (\Exception $e) {
            return redirect('places')->with('error', 'Houve um problema ao criar a morada. Tente novamente.');
        }
    }

    public function showCreatePlaceForm()
    {
        return Inertia::render('Places/NewPlace');
    }

    public function showEditScreen(Place $place)
    {
        $kids = Kid::all();
        return Inertia::render('Places/Edit', ['place' => $place, 'kids' => $kids]);
    }

    public function editPlace(Place $place, Request $request)
    {
        $incomingFields = $request->validate([
            'address' => 'required',
            'known_as' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);
        $incomingFields['latitude'] = strip_tags($incomingFields['latitude']);
        $incomingFields['longitude'] = strip_tags($incomingFields['longitude']);

        try {
            $place->update($incomingFields);
            return redirect('/places')->with('message', 'Morada editada com sucesso');
        } catch (\Exception $e) {
            return redirect('/places')->with('error', 'Houve um problema ao editar a morada. Tente novamente.');
        }
    }

    public function deletePlace($id)
    {
        $place = Place::findOrFail($id);
        $place->delete();

        return redirect('/places');
    }
}
